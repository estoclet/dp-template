<?php

namespace Drupal\mini_sites;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\mini_sites\Entity\MiniSitePageInterface;

/**
 * Defines a Controller class for mini site pages.
 */
class MiniSitePageStorage extends SqlContentEntityStorage {

  /**
   * Array of page parents keyed by mini site ID and child page ID.
   *
   * @var array
   */
  protected $treeParents = [];

  /**
   * Array of page children keyed by mini site ID and parent page ID.
   *
   * @var array
   */
  protected $treeChildren = [];

  /**
   * Array of pages in a tree keyed by mini site ID and page ID.
   *
   * @var array
   */
  protected $treePages = [];

  /**
   * Array of loaded trees keyed by a cache id matching tree arguments.
   *
   * @var array
   */
  protected $trees = [];

  /**
   * Array of all loaded term ancestry keyed by ancestor term ID, keyed by term
   * ID.
   *
   * @var \Drupal\taxonomy\TermInterface[][]
   */
  protected $ancestors;

  /**
   * {@inheritdoc}
   *
   * @param array $values
   *   An array of values to set, keyed by property name. A value for the
   *   mini site ID ('mini_site') is required.
   */
  public function create(array $values = []) {
    // Save new page with no parents by default.
    if (empty($values['parent'])) {
      $values['parent'] = [0];
    }

    $values['mini_site'] = \Drupal::request()->get('mini_site');

    $entity = parent::create($values);
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function loadTree($miniSiteId, $parent = 0, $max_depth = NULL, $load_entities = FALSE) {
    $cache_key = implode(':', func_get_args());
    if (!isset($this->trees[$cache_key])) {
      // We cache trees, so it's not CPU-intensive to call on a page and its
      // children, too.
      if (!isset($this->treeChildren[$miniSiteId])) {
        $this->treeChildren[$miniSiteId] = [];
        $this->treeParents[$miniSiteId] = [];
        $this->treePages[$miniSiteId] = [];
        $query = $this->database->select($this->getDataTable(), 'p');
        $result = $query
          ->addTag('mini_site_page_access')
          ->fields('p')
          ->condition('p.mini_site', $miniSiteId)
          ->orderBy('p.weight')
          ->orderBy('p.id')
          ->execute();
        foreach ($result as $page) {
          $this->treeChildren[$miniSiteId][$page->parent][] = $page->id;
          $this->treeParents[$miniSiteId][$page->id][] = $page->parent;
          $this->treePages[$miniSiteId][$page->id] = $page;
        }
      }

      // Load full entities, if necessary. The entity controller statically
      // caches the results.
      $page_entities = [];
      if ($load_entities) {
        $page_entities = $this->loadMultiple(array_keys($this->treePages[$miniSiteId]));
      }

      $max_depth = (!isset($max_depth)) ? count($this->treeChildren[$miniSiteId]) : $max_depth;
      $tree = [];

      // Keeps track of the parents we have to process, the last entry is used
      // for the next processing step.
      $process_parents = [];
      $process_parents[] = $parent;

      // Loops over the parent pages and adds its children to the tree array.
      // Uses a loop instead of a recursion, because it's more efficient.
      while (count($process_parents)) {
        $parent = array_pop($process_parents);
        // The number of parents determines the current depth.
        $depth = count($process_parents);
        if ($max_depth > $depth && !empty($this->treeChildren[$miniSiteId][$parent])) {
          $has_children = FALSE;
          $child = current($this->treeChildren[$miniSiteId][$parent]);
          do {
            if (empty($child)) {
              break;
            }
            /** @var $page \Drupal\Core\Entity\EntityInterface */
            $page = $load_entities ? $page_entities[$child] : $this->treePages[$miniSiteId][$child];
            if (isset($this->treeParents[$miniSiteId][$load_entities ? $page->id() : $page->id])) {
              // Clone the page so that the depth attribute remains correct
              // in the event of multiple parents.
              $page = clone $page;
            }
            $page->depth = $depth;
            if (!$load_entities) {
              unset($page->parent);
            }
            $pageId = $load_entities ? $page->id() : $page->id;
            $page->parents = $this->treeParents[$miniSiteId][$pageId];
            $tree[] = $page;
            if (!empty($this->treeChildren[$miniSiteId][$pageId])) {
              $has_children = TRUE;

              // We have to continue with this parent later.
              $process_parents[] = $parent;
              // Use the current page as parent for the next iteration.
              $process_parents[] = $pageId;

              // Reset pointers for child lists because we step in there more
              // often with multi parents.
              reset($this->treeChildren[$miniSiteId][$pageId]);
              // Move pointer so that we get the correct page the next time.
              next($this->treeChildren[$miniSiteId][$parent]);
              break;
            }
          } while ($child = next($this->treeChildren[$miniSiteId][$parent]));

          if (!$has_children) {
            // We processed all pages in this hierarchy-level, reset pointer
            // so that this function works the next time it gets called.
            reset($this->treeChildren[$miniSiteId][$parent]);
          }
        }
      }
      $this->trees[$cache_key] = $tree;
    }
    return $this->trees[$cache_key];
  }

  /**
   * {@inheritdoc}
   */
  public function resetWeights($miniSiteId) {
    $this->database->update($this->getDataTable())
      ->fields(['weight' => 0])
      ->condition('mini_site', $miniSiteId)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function loadAllParents($id) {
    /** @var MiniSitePageInterface $miniSitePage */
    return (!empty($id) && $miniSitePage = $this->load($id)) ? $this->getAncestors($miniSitePage) : [];
  }

  /**
   * Returns all children page of this page.
   *
   * @return \Drupal\mini_sites\Entity\MiniSitePage[]
   *   A list of children pages entities keyed by page ID.
   *
   * @internal
   */
  public function getChildren(MiniSitePageInterface $page) {
    $query = \Drupal::entityQuery('mini_site_page')
      ->condition('parent', $page->id());
    return static::loadMultiple($query->execute());
  }

  /**
   * Returns all ancestors of this page.
   *
   * @return MiniSitePageInterface[]
   *   A list of ancestor mini site page entities keyed by ID.
   *
   * @internal
   * @todo Refactor away when TreeInterface is introduced.
   */
  protected function getAncestors(MiniSitePageInterface $miniSitePage) {
    if (!isset($this->ancestors[$miniSitePage->id()])) {
      $this->ancestors[$miniSitePage->id()] = [$miniSitePage->id() => $miniSitePage];
      $search[] = $miniSitePage->id();

      while ($tid = array_shift($search)) {
        foreach ($this->getParents(static::load($tid)) as $id => $parent) {
          if ($parent && !isset($this->ancestors[$miniSitePage->id()][$id])) {
            $this->ancestors[$miniSitePage->id()][$id] = $parent;
            $search[] = $id;
          }
        }
      }
    }
    return $this->ancestors[$miniSitePage->id()];
  }

  /**
   * Returns a list of parents of this mini site page.
   *
   * @return MiniSitePageInterface[]
   *   The parent pages entities keyed by ID. If this page has a
   *   <root> parent, that item is keyed with 0 and will have NULL as value.
   *
   * @internal
   * @todo Refactor away when TreeInterface is introduced.
   */
  protected function getParents(MiniSitePageInterface $miniSitePage) {
    $parents = $ids = [];
    // Cannot use $this->get('parent')->referencedEntities() here because that
    // strips out the '0' reference.
    foreach ($miniSitePage->get('parent') as $item) {
      if ($item->target_id == 0) {
        // The <root> parent.
        $parents[0] = NULL;
        continue;
      }
      $ids[] = $item->target_id;
    }

    // @todo Better way to do this? AND handle the NULL/0 parent?
    // Querying the terms again so that the same access checks are run when
    // getParents() is called as in Drupal version prior to 8.3.
    $loaded_parents = [];

    if ($ids) {
      $query = \Drupal::entityQuery('mini_site_page')
        ->condition('id', $ids, 'IN');

      $loaded_parents = static::loadMultiple($query->execute());
    }

    return $parents + $loaded_parents;
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    $vars = parent::__sleep();
    // Do not serialize static cache.
    unset($vars['ancestors'], $vars['treeChildren'], $vars['treeParents'], $vars['treePages'], $vars['trees']);
    return $vars;
  }

  /**
   * {@inheritdoc}
   */
  public function __wakeup() {
    parent::__wakeup();
    // Initialize static caches.
    $this->ancestors = [];
    $this->treeChildren = [];
    $this->treeParents = [];
    $this->treePages = [];
    $this->trees = [];
  }

}

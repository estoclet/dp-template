<?php

namespace Drupal\mini_sites\Form;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\DependencyInjection\DeprecatedServicePropertyTrait;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\mini_sites\Entity\MiniSiteInterface;
use Drupal\mini_sites\Entity\MiniSitePageType;
use Drupal\mini_sites\Entity\MiniSiteType;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides pages overview form for a mini site.
 *
 * @internal
 */
class OverviewMiniSitePage extends FormBase {
  use DeprecatedServicePropertyTrait;

  /**
   * {@inheritdoc}
   */
  protected $deprecatedProperties = ['entityManager' => 'entity.manager'];

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The mini site page storage handler.
   *
   * @var \Drupal\mini_sites\MiniSitePageStorage
   */
  protected $storageController;

  /**
   * The page list builder.
   *
   * @var \Drupal\mini_sites\MiniSitePageListBuilder
   */
  protected $miniSitePageListBuilder;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Constructs an OverviewMiniSitePage object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   */
  public function __construct(ModuleHandlerInterface $module_handler, EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer = NULL, EntityRepositoryInterface $entity_repository = NULL) {
    $this->moduleHandler = $module_handler;
    $this->entityTypeManager = $entity_type_manager;
    $this->storageController = $entity_type_manager->getStorage('mini_site_page');
    $this->miniSitePageListBuilder = $entity_type_manager->getListBuilder('mini_site_page');
    $this->renderer = $renderer ?: \Drupal::service('renderer');
    if (!$entity_repository) {
      @trigger_error('Calling OverviewMiniSitePage::__construct() with the $entity_repository argument is supported in drupal:8.7.0 and will be required before drupal:9.0.0. See https://www.drupal.org/node/2549139.', E_USER_DEPRECATED);
      $entity_repository = \Drupal::service('entity.repository');
    }
    $this->entityRepository = $entity_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('entity_type.manager'),
      $container->get('renderer'),
      $container->get('entity.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mini_sites_overview_page';
  }

  /**
   * Form constructor.
   *
   * Display a tree of all the pages in a mini site, with options to edit
   * each one. The form is made drag and drop by the theme function.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Drupal\mini_sites\Entity\MiniSiteInterface $mini_site
   *   The mini site to display the page overview form for.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, MiniSiteInterface $mini_site = NULL) {
    // @todo Remove global variables when https://www.drupal.org/node/2044435 is
    //   in.
    global $pager_page_array, $pager_total, $pager_total_items;

    $form_state->set('mini_site', $mini_site);
    $parent_fields = FALSE;

    $page = $this->getRequest()->query->get('page') ?: 0;
    // Number of pages per page.
    $page_increment = 20;
    // Elements shown on this page.
    $page_entries = 0;
    // Elements at the root level before this page.
    $before_entries = 0;
    // Elements at the root level after this page.
    $after_entries = 0;
    // Elements at the root level on this page.
    $root_entries = 0;

    // Pages from previous and next pages are shown if the page tree would have
    // been cut in the middle. Keep track of how many extra pages we show on
    // each page of pages.
    $back_step = NULL;
    $forward_step = 0;

    // An array of the pages to be displayed on this page.
    $current_page = [];

    $delta = 0;
    $page_deltas = [];
    $tree = $this->storageController->loadTree($mini_site->id(), 0, NULL, TRUE);
    $tree_index = 0;
    do {
      // In case this tree is completely empty.
      if (empty($tree[$tree_index])) {
        break;
      }
      $delta++;
      // Count entries before the current page.
      if ($page && ($page * $page_increment) > $before_entries && !isset($back_step)) {
        $before_entries++;
        continue;
      }
      // Count entries after the current page.
      elseif ($page_entries > $page_increment && isset($complete_tree)) {
        $after_entries++;
        continue;
      }

      // Do not let a miniSitePage start the page that is not at the root.
      $miniSitePage = $tree[$tree_index];
      if (isset($miniSitePage->depth) && ($miniSitePage->depth > 0) && !isset($back_step)) {
        $back_step = 0;
        while ($ppage = $tree[--$tree_index]) {
          $before_entries--;
          $back_step++;
          if ($ppage->depth == 0) {
            $tree_index--;
            // Jump back to the start of the root level parent.
            continue 2;
          }
        }
      }
      $back_step = isset($back_step) ? $back_step : 0;

      // Continue rendering the tree until we reach the a new root item.
      if ($page_entries >= $page_increment + $back_step + 1 && $miniSitePage->depth == 0 && $root_entries > 1) {
        $complete_tree = TRUE;
        // This new item at the root level is the first item on the next page.
        $after_entries++;
        continue;
      }
      if ($page_entries >= $page_increment + $back_step) {
        $forward_step++;
      }

      // Finally, if we've gotten down this far, we're rendering a miniSitePage on this
      // page.
      $page_entries++;
      $page_deltas[$miniSitePage->id()] = isset($page_deltas[$miniSitePage->id()]) ? $page_deltas[$miniSitePage->id()] + 1 : 0;
      $key = 'id:' . $miniSitePage->id() . ':' . $page_deltas[$miniSitePage->id()];

      // Keep track of the first miniSitePage displayed on this page.
      if ($page_entries == 1) {
        $form['#first_id'] = $miniSitePage->id();
      }
      // Keep a variable to make sure at least 2 root elements are displayed.
      if ($miniSitePage->parents[0] == 0) {
        $root_entries++;
      }
      $current_page[$key] = $miniSitePage;
    } while (isset($tree[++$tree_index]));

    // Because we didn't use a pager query, set the necessary pager variables.
    $total_entries = $before_entries + $page_entries + $after_entries;
    $pager_total_items[0] = $total_entries;
    $pager_page_array[0] = $page;
    $pager_total[0] = ceil($total_entries / $page_increment);

    // If this form was already submitted once, it's probably hit a validation
    // error. Ensure the form is rebuilt in the same order as the user
    // submitted.
    $user_input = $form_state->getUserInput();
    if (!empty($user_input)) {
      // Get the POST order.
      $order = array_flip(array_keys($user_input['miniSitePages']));
      // Update our form with the new order.
      $current_page = array_merge($order, $current_page);
      foreach ($current_page as $key => $miniSitePage) {
        // Verify this is a miniSitePage for the current page and set at the current
        // depth.
        if (is_array($user_input['miniSitePages'][$key]) && is_numeric($user_input['miniSitePages'][$key]['miniSitePage']['id'])) {
          $current_page[$key]->depth = $user_input['miniSitePages'][$key]['miniSitePage']['depth'];
        }
        else {
          unset($current_page[$key]);
        }
      }
    }

    $args = [
      '%capital_name' => Unicode::ucfirst($mini_site->label()),
      '%name' => $mini_site->label(),
    ];
    if ($this->currentUser()->hasPermission('administer mini site page entities')) {
      $help_message = $this->t('%capital_name contains pages grouped under root. You can reorganize the pages in %capital_name using their drag-and-drop handles.', $args);
    }
    else {
      $help_message = $this->t('%capital_name contains pages grouped under root', $args);
    }

    $update_tree_access = AccessResult::allowed();
    $form['help'] = [
      '#type' => 'container',
      'message' => ['#markup' => $help_message],
    ];

    $errors = $form_state->getErrors();
    $row_position = 0;
    // Build the actual form.
    $access_control_handler = $this->entityTypeManager->getAccessControlHandler('mini_site_page');
    $create_access = $access_control_handler->createAccess($mini_site->id(), NULL, [], TRUE);
    if ($create_access->isAllowed()) {
      $empty = $this->t('No page available. <a href=":link">Add Page</a>.', [':link' => Url::fromRoute('entity.mini_site_page.add_page', ['mini_site' => $mini_site->id()])->toString()]);
    }
    else {
      $empty = $this->t('No pages available.');
    }
    $form['miniSitePages'] = [
      '#type' => 'table',
      '#empty' => $empty,
      '#header' => [
        'miniSitePage' => $this->t('Name'),
        'miniSitePageType' => $this->t('Type'),
        'operations' => $this->t('Operations'),
        'weight' => $this->t('Weight'),
      ],
      '#attributes' => [
        'id' => 'mini-site-page',
      ],
    ];
    $this->renderer->addCacheableDependency($form['miniSitePages'], $create_access);

    foreach ($current_page as $key => $miniSitePage) {
      $form['miniSitePages'][$key] = [
        'miniSitePage' => [],
        'miniSitePageType' => [],
        'operations' => [],
        'weight' => [],
      ];
      /** @var $miniSitePage \Drupal\mini_sites\Entity\MiniSitePage */
      $miniSitePage = $this->entityRepository->getTranslationFromContext($miniSitePage);
      $form['miniSitePages'][$key]['#miniSitePage'] = $miniSitePage;
      $indentation = [];
      if (isset($miniSitePage->depth) && $miniSitePage->depth > 0) {
        $indentation = [
          '#theme' => 'indentation',
          '#size' => $miniSitePage->depth,
        ];
      }
      $form['miniSitePages'][$key]['miniSitePage'] = [
        '#prefix' => !empty($indentation) ? $this->renderer->render($indentation) : '',
        '#type' => 'link',
        '#title' => $miniSitePage->label(),
        '#url' => $miniSitePage->toUrl(),
      ];
      $form['miniSitePages'][$key]['miniSitePageType'] = [
        '#markup' => MiniSitePageType::load($miniSitePage->bundle())->label(),
      ];

      if (count($tree) > 1) {
        $parent_fields = TRUE;
        $form['miniSitePages'][$key]['miniSitePage']['id'] = [
          '#type' => 'hidden',
          '#value' => $miniSitePage->id(),
          '#attributes' => [
            'class' => ['miniSitePage-id'],
          ],
        ];
        $form['miniSitePages'][$key]['miniSitePage']['parent'] = [
          '#type' => 'hidden',
          // Yes, default_value on a hidden. It needs to be changeable by the
          // javascript.
          '#default_value' => $miniSitePage->parents[0],
          '#attributes' => [
            'class' => ['miniSitePage-parent'],
          ],
        ];
        $form['miniSitePages'][$key]['miniSitePage']['depth'] = [
          '#type' => 'hidden',
          // Same as above, the depth is modified by javascript, so it's a
          // default_value.
          '#default_value' => $miniSitePage->depth,
          '#attributes' => [
            'class' => ['miniSitePage-depth'],
          ],
        ];
      }
      $update_access = $miniSitePage->access('update', NULL, TRUE);
      $update_tree_access = $update_access;

      if ($update_tree_access->isAllowed()) {
        $form['miniSitePages'][$key]['weight'] = [
          '#type' => 'weight',
          '#delta' => $delta,
          '#title' => $this->t('Weight for added miniSitePage'),
          '#title_display' => 'invisible',
          '#default_value' => $miniSitePage->getWeight(),
          '#attributes' => ['class' => ['miniSitePage-weight']],
        ];
      }

      if ($operations = $this->miniSitePageListBuilder->getOperations($miniSitePage)) {
        $form['miniSitePages'][$key]['operations'] = [
          '#type' => 'operations',
          '#links' => $operations,
        ];
      }

      if ($parent_fields) {
        $form['miniSitePages'][$key]['#attributes']['class'][] = 'draggable';
      }

      // Add classes that mark which miniSitePages belong to previous and next pages.
      if ($row_position < $back_step || $row_position >= $page_entries - $forward_step) {
        $form['miniSitePages'][$key]['#attributes']['class'][] = 'mini-site-pages-preview';
      }

      if ($row_position !== 0 && $row_position !== count($tree) - 1) {
        if ($row_position == $back_step - 1 || $row_position == $page_entries - $forward_step - 1) {
          $form['miniSitePages'][$key]['#attributes']['class'][] = 'mini-site-pages-divider-top';
        }
        elseif ($row_position == $back_step || $row_position == $page_entries - $forward_step) {
          $form['miniSitePages'][$key]['#attributes']['class'][] = 'mini-site-pages-divider-bottom';
        }
      }

      // Add an error class if this row contains a form error.
      foreach ($errors as $error_key => $error) {
        if (strpos($error_key, $key) === 0) {
          $form['miniSitePages'][$key]['#attributes']['class'][] = 'error';
        }
      }
      $row_position++;
    }

    $this->renderer->addCacheableDependency($form['miniSitePages'], $update_tree_access);
    if ($update_tree_access->isAllowed()) {
      if ($parent_fields) {
        $form['miniSitePages']['#tabledrag'][] = [
          'action' => 'match',
          'relationship' => 'parent',
          'group' => 'miniSitePage-parent',
          'subgroup' => 'miniSitePage-parent',
          'source' => 'miniSitePage-id',
          'hidden' => FALSE,
        ];
        $form['miniSitePages']['#tabledrag'][] = [
          'action' => 'depth',
          'relationship' => 'group',
          'group' => 'miniSitePage-depth',
          'hidden' => FALSE,
        ];
        $form['miniSitePages']['#attached']['library'][] = 'mini_sites/mini_sites.overview';
        $form['miniSitePages']['#attached']['drupalSettings']['mini_sites'] = [
          'backStep' => $back_step,
          'forwardStep' => $forward_step,
        ];
      }
      $form['miniSitePages']['#tabledrag'][] = [
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => 'miniSitePage-weight',
      ];
    }

    if ($update_tree_access->isAllowed() && count($tree) > 1) {
      $form['actions'] = ['#type' => 'actions', '#tree' => FALSE];
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
        '#button_type' => 'primary',
      ];
      $form['actions']['reset_alphabetical'] = [
        '#type' => 'submit',
        '#submit' => ['::submitReset'],
        '#value' => $this->t('Reset to alphabetical'),
      ];
    }

    $form['pager_pager'] = ['#type' => 'pager'];
    return $form;
  }

  /**
   * Form submission handler.
   *
   * Rather than using a textfield or weight field, this form depends entirely
   * upon the order of form elements on the page to determine new weights.
   *
   * Because there might be hundreds or thousands of pages that need to
   * be ordered, pages are weighted from 0 to the number of pages in the
   * mini site, rather than the standard -10 to 10 scale. Numbers are sorted
   * lowest to highest, but are not necessarily sequential. Numbers may be
   * skipped when a miniSitePage has children so that reordering is minimal when a child
   * is added or removed from a miniSitePage.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Sort miniSitePage order based on weight.
    uasort($form_state->getValue('miniSitePages'), ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);

    $miniSite = $form_state->get('mini_site');
    $changed_pages = [];
    $tree = $this->storageController->loadTree($miniSite->id(), 0, NULL, TRUE);

    if (empty($tree)) {
      return;
    }

    // Build a list of all pages that need to be updated on previous pages.
    $weight = 0;
    $miniSitePage = $tree[0];
    while ($miniSitePage->id() != $form['#first_id']) {
      if ($miniSitePage->parents[0] == 0 && $miniSitePage->getWeight() != $weight) {
        $miniSitePage->setWeight($weight);
        $changed_pages[$miniSitePage->id()] = $miniSitePage;
      }
      $weight++;
      $miniSitePage = $tree[$weight];
    }

    // Renumber the current page weights and assign any new parents.
    $level_weights = [];
    foreach ($form_state->getValue('miniSitePages') as $id => $values) {
      if (isset($form['miniSitePages'][$id]['#miniSitePage'])) {
        $miniSitePage = $form['miniSitePages'][$id]['#miniSitePage'];
        // Give pages at the root level a weight in sequence with pages on previous pages.
        if ($values['miniSitePage']['parent'] == 0 && $miniSitePage->getWeight() != $weight) {
          $miniSitePage->setWeight($weight);
          $changed_pages[$miniSitePage->id()] = $miniSitePage;
        }
        // Pages not at the root level can safely start from 0 because they're all on this page.
        elseif ($values['miniSitePage']['parent'] > 0) {
          $level_weights[$values['miniSitePage']['parent']] = isset($level_weights[$values['miniSitePage']['parent']]) ? $level_weights[$values['miniSitePage']['parent']] + 1 : 0;
          if ($level_weights[$values['miniSitePage']['parent']] != $miniSitePage->getWeight()) {
            $miniSitePage->setWeight($level_weights[$values['miniSitePage']['parent']]);
            $changed_pages[$miniSitePage->id()] = $miniSitePage;
          }
        }
        // Update any changed parents.
        if ($values['miniSitePage']['parent'] != $miniSitePage->parents[0]) {
          $miniSitePage->parent->target_id = $values['miniSitePage']['parent'];
          $changed_pages[$miniSitePage->id()] = $miniSitePage;
        }
        $weight++;
      }
    }

    // Build a list of all pages that need to be updated on following pages.
    for ($weight; $weight < count($tree); $weight++) {
      $miniSitePage = $tree[$weight];
      if ($miniSitePage->parents[0] == 0 && $miniSitePage->getWeight() != $weight) {
        $miniSitePage->parent->target_id = $miniSitePage->parents[0];
        $miniSitePage->setWeight($weight);
        $changed_pages[$miniSitePage->id()] = $miniSitePage;
      }
    }

    if (!empty($changed_pages)) {
      // Save all updated pages.
      foreach ($changed_pages as $miniSitePage) {
        $miniSitePage->save();
      }

      $this->messenger()->addStatus($this->t('The configuration options have been saved.'));
    }
  }

  /**
   * Redirects to confirmation form for the reset action.
   */
  public function submitReset(array &$form, FormStateInterface $form_state) {
    /** @var $miniSite \Drupal\mini_sites\Entity\MiniSiteInterface */
    $miniSite = $form_state->get('mini_site');
    $this->storageController->resetWeights($miniSite->id());
    $this->messenger()->addStatus($this->t('Reset pages from site %name to alphabetical order.', ['%name' => $miniSite->label()]));
    $this->logger('taxonomy')->notice('Reset pages from site %name to alphabetical order.', ['%name' => $miniSite->label()]);
    $form_state->setRedirect('entity.mini_site_page.collection', ['mini_site' => $miniSite->id()]);
  }

}

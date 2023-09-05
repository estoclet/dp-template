<?php

namespace Drupal\mini_sites\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\mini_sites\Entity\MiniSite;
use Drupal\mini_sites\Entity\MiniSitePage;
use Drupal\mini_sites\MiniSiteHelper;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provides a 'Mini Site Menu' block.
 *
 * @Block(
 *  id = "mini_site_menu",
 *  admin_label = @Translation("Mini Site Menu"),
 *  category = @Translation("Mini site")
 * )
 */
class MiniSiteMenu extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The mini site page entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $miniSitePageStorage;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The mini site helper.
   *
   * @var \Drupal\mini_sites\MiniSiteHelper
   */
  protected $miniSiteHelper;

  /**
   * The array of mini_site_entity_reference fields.
   *
   * @var array
   */
  protected $refFields;

  /**
   * Overrides the construction of context aware plugins to allow for
   * unvalidated constructor based injection of contexts.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param RouteMatchInterface $route_match
   *   The route match service
   * @param array $refFields
   *   The array of mini_site_entity_reference fields
   * @param MiniSiteHelper $miniSiteHelper
   *   The mini site helper
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, RouteMatchInterface $route_match, array $refFields, MiniSiteHelper $miniSiteHelper)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->miniSitePageStorage = $entityTypeManager->getStorage('mini_site_page');
    $this->routeMatch = $route_match;
    $this->refFields = [];
    foreach($refFields['mini_site_page'] as $currentFieldName => $currentField) {
      foreach($currentField['bundles'] as $bundle) {
        if(!isset($this->refFields[$bundle])) {
          $this->refFields[$bundle] = [];
        }
        $this->refFields[$bundle][] = $currentFieldName;
      }
    }

    $this->miniSiteHelper = $miniSiteHelper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_route_match'),
      $container->get('entity_field.manager')->getFieldMapByFieldType('mini_site_entity_reference'),
      $container->get('mini_sites.mini_site_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $miniSitePageEntity = NULL;
    /**
     * @var \Drupal\mini_sites\Entity\MiniSite $miniSiteEntity
     */
    $miniSiteEntity = $this->miniSiteHelper->getCurrentMiniSite($miniSitePageEntity);

    if(!$miniSiteEntity) {
      return [];
    }

    $flatTree = $this->miniSitePageStorage->loadTree($miniSiteEntity->id());
    $index=array();
    $tree=array();
    foreach ($flatTree as $key => $currentItem) {
      if($currentItem->type === 'link' && ($page = MiniSitePage::load($currentItem->id))) {
        $language = \Drupal::languageManager()->getCurrentLanguage(\Drupal\Core\Language\LanguageInterface::TYPE_CONTENT)->getId();
        $variables['language'] = $language;
        if ($page->hasTranslation($language)) {
          $page = \Drupal::service('entity.repository')->getTranslationFromContext($page, $language);
        }
        $value = $page->get('field_link')->getValue()[0];
        $libelle = $page->get('field_libelle')->getValue()[0]['value'];
        \Drupal::logger('ciclade_service_ws_response')->warning('<pre><code>' . print_r($libelle, TRUE) . '</code></pre>');
        $name=(!empty($libelle))?$libelle:$currentItem->name;
        $linkObj = Link::fromTextAndUrl($name, Url::fromUri($value['uri']),['language'=>$language]);
        $active = NULL;
      }
      else {
        $active = $miniSitePageEntity && $miniSitePageEntity->id() == $currentItem->id;
        $linkObj = Link::createFromRoute($currentItem->name, 'entity.mini_site_page.canonical', ['mini_site_page' => $currentItem->id], ['attributes' => ['class' => $active ? 'active' : '' ]]);
      }
      \Drupal::moduleHandler()->alter('mini_site_page_menu_item', $linkObj, $currentItem, $active);

      $elem = [
        'link' => $linkObj->toRenderable(),
        'children' => []
      ];
      if($linkObj->getUrl()->isExternal()) {
        $elem['link']['#attributes'] = [
          'target' => '_blank'
        ];
      }
      if ($currentItem->parents[0] == 0) {
        $tree[$currentItem->id] = $elem;
        $index[$currentItem->id] = &$tree[$currentItem->id];
      } else if (isset($index[$currentItem->parents[0]])) {
        $index[$currentItem->parents[0]]['children'][$currentItem->id] = $elem;
        $index[$currentItem->id]=&$index[$currentItem->parents[0]]['children'][$currentItem->id];
      }
    }

    $build['mini_site_menu'] = [
      '#theme' => 'mini_site_menu' . '__' . $miniSiteEntity->type->target_id,
      '#mini_site_page' => $miniSitePageEntity,
      '#mini_site' => $miniSiteEntity,
      '#items' => $flatTree,
      'content' => [
        '#theme' => 'item_list',
        '#items' => $tree
      ],
    ];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    /**
     * @var \Drupal\mini_sites\Entity\MiniSite $miniSiteEntity
     */
    $miniSiteEntity = $this->miniSiteHelper->getCurrentMiniSite($miniSitePageEntity);
    if(!$miniSiteEntity) {
      return parent::getCacheTags();
    }
    $cache = ['mini_site:' . $miniSiteEntity->id()];
    $flatTree = $this->miniSitePageStorage->loadTree($miniSiteEntity->id(), 0, NULL, TRUE);
    foreach ($flatTree as $key => $currentItem) {
      $cache[] = 'mini_site_page:' . $currentItem->id();
      if(isset($this->refFields[$currentItem->bundle()])) {
        foreach($this->refFields[$currentItem->bundle()] as $currentField) {
          $cache[] = 'node:' . $currentItem->{$currentField}->target_id;
        }
      }
    }

    return Cache::mergeTags(parent::getCacheTags(), $cache);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['url.path']);
  }

}

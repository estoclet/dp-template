<?php

namespace Drupal\icdc_menu_mobile\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Menu\MenuActiveTrailInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\views\ViewExecutableFactory;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This block is used to display ICDC Menu mobile.
 *
 * @Block(
 *  id = "icdc_menu_mobile",
 *  admin_label = @Translation("ICDC Menu mobile"),
 *  category = @Translation("ICDC")
 * )
 */
class IcdcMenuMobile extends BlockBase implements ContainerFactoryPluginInterface  {

  /**
   * The Plugin Menu Tree.
   *
   * @var \Drupal\Core\Menu\MenuLinkTree
   */
  protected $menuTree;

  /**
   * The active menu trail service.
   *
   * @var \Drupal\Core\Menu\MenuActiveTrailInterface
   */
  protected $menuActiveTrail;

  /**
   * The Plugin Manager Block
   *
   * @var \Drupal\Core\Block\BlockManager
   */
  protected $blockManager;

  /**
   * The View executable object.
   *
   * @var \Drupal\views\ViewExecutable
   */
  protected $view;

  /**
   * The display ID being used for this View.
   *
   * @var string
   */
  protected $displayID;

  /**
   * Indicates whether the display was successfully set.
   *
   * @var bool
   */
  protected $displaySet;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * Constructs a new SystemMenuBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menu_tree
   *   The menu tree service.
   * @param \Drupal\Core\Menu\MenuActiveTrailInterface $menu_active_trail
   *   The active menu trail service.
   * @param \Drupal\views\ViewExecutableFactory $executable_factory
   *   The view executable factory.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The views storage.
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The current user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MenuLinkTreeInterface $menuTree, MenuActiveTrailInterface $menuActiveTrail, BlockManagerInterface $blockManager, ViewExecutableFactory $executable_factory, EntityStorageInterface $storage, AccountInterface $user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->menuTree = $menuTree;
    $this->menuActiveTrail = $menuActiveTrail;
    $this->blockManager = $blockManager;
    $this->displayID = 'page_1';
    // Load the view.
    $view = $storage->load('recherche');
    $this->view = $executable_factory->get($view);
    $this->displaySet = $this->view->setDisplay($this->displayID);
    $this->user = $user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('menu.link_tree'),
      $container->get('menu.active_trail'),
      $container->get('plugin.manager.block'),
      $container->get('views.executable'),
      $container->get('entity_type.manager')->getStorage('view'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['#theme'] = 'icdc_menu_mobile_block';

    $this->view->initHandlers();
    if ($this->view->display_handler->usesExposed()) {
      /** @var \Drupal\views\Plugin\views\exposed_form\ExposedFormPluginInterface $exposed_form */
      $exposed_form = $this->view->display_handler->getPlugin('exposed_form');
      $output =  $exposed_form->renderExposedForm(TRUE);
    }

    if (is_array($output) && !empty($output)) {
      $output += [
        '#view' => $this->view,
        '#display_id' => $this->displayID,
      ];
      $build['#search'] = $output;
    }

    $menu = [
      'main' => '#main',
      'menu-profil' => '#menuprofil'
    ];
    foreach($menu as $menu_name => $menu_var_name) {
      $build[$menu_var_name] = $this->buildMenu($menu_name);
    }
    return $build;
  }

  protected function buildMenu($menu_name) {
    $parameters = new MenuTreeParameters();
    $active_trail = $this->menuActiveTrail->getActiveTrailIds($menu_name);
    $parameters->setActiveTrail($active_trail);
    $parameters->setMinDepth(0);
    $tree = $this->menuTree->load($menu_name, $parameters);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $this->menuTree->transform($tree, $manipulators);
    $build = $this->menuTree->build($tree);
    if($menu_name === 'main' && !empty($build['#items']['views_view:views.blog.page_accueil_blog']['below'])) {
      $build['#items']['views_view:views.blog.page_accueil_blog']['below'] = [];
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $cache_tags = parent::getCacheTags();
    $cache_tags[] = 'config:system.menu.main';
    $cache_tags[] = 'config:system.menu.menu-profil';
    $cache_tags[] = 'config:views.view.recherche';
    return $cache_tags;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route.menu_active_trails:main', 'route.menu_active_trails:menu-profil']);
  }
}

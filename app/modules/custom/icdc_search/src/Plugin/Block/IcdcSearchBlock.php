<?php

namespace Drupal\icdc_search\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\views\ViewExecutableFactory;
use Drupal\views\Views;
use Drupal\Core\Form\FormState;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This block is used to display ICDC Search Block.
 *
 * @Block(
 *  id = "icdc_search_block",
 *  admin_label = @Translation("ICDC Search block"),
 *  category = @Translation("ICDC")
 * )
 */
class IcdcSearchBlock extends BlockBase implements ContainerFactoryPluginInterface {
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
   * Constructs a \Drupal\views\Plugin\Block\ViewsBlockBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\views\ViewExecutableFactory $executable_factory
   *   The view executable factory.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The views storage.
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The current user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ViewExecutableFactory $executable_factory, EntityStorageInterface $storage, AccountInterface $user) {
    $this->displayID = 'page_1';
    // Load the view.
    $view = $storage->load('recherche');
    $this->view = $executable_factory->get($view);
    $this->displaySet = $this->view->setDisplay($this->displayID);
    $this->user = $user;

    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('views.executable'),
      $container->get('entity_type.manager')->getStorage('view'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['#theme'] = 'icdc_search_block';
    $build['#title'] = $this->t('Search');
    $build['#attached']['library'][] = 'icdc_search/icdc_search.search_modal';

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
      $build['#content'] = $output;
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    if ($this->view->access($this->displayID)) {
      $access = AccessResult::allowed();
    }
    else {
      $access = AccessResult::forbidden();
    }
    return $access;
  }

  public function getCacheContexts() {
    $cache_contexts = parent::getCacheContexts();
    $cache_contexts[] = 'url.query_args:search_api_fulltext';
    return $cache_contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $cache_tags = parent::getCacheTags();
    $cache_tags[] = 'config:views.view.recherche';
    return $cache_tags;
  }
}

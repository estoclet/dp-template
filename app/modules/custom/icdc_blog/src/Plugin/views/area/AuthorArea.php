<?php

/**
 * @file
 * Contains \Drupal\icdc_blog\Plugin\views\area\AuthorArea.
 */
namespace Drupal\icdc_blog\Plugin\views\area;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\views\Plugin\views\area\AreaPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Views area AuthorArea handler.
 *
 * @ingroup views_area_handlers
 *
 * @ViewsArea("author_area")
 */
class AuthorArea extends AreaPluginBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * Constructs a new Entity instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityRepositoryInterface $entity_repository = NULL, EntityDisplayRepositoryInterface $entity_display_repository = NULL) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;

    if (!$entity_repository) {
      @trigger_error('Calling EntityRow::__construct() with the $entity_repository argument is supported in drupal:8.7.0 and will be required before drupal:9.0.0. See https://www.drupal.org/node/2549139.', E_USER_DEPRECATED);
      $entity_repository = \Drupal::service('entity.repository');
    }
    $this->entityRepository = $entity_repository;

    if (!$entity_display_repository) {
      @trigger_error('Calling EntityRow::__construct() with the $entity_display_repository argument is supported in drupal:8.7.0 and will be required before drupal:9.0.0. See https://www.drupal.org/node/2549139.', E_USER_DEPRECATED);
      $entity_display_repository = \Drupal::service('entity_display.repository');
    }
    $this->entityDisplayRepository = $entity_display_repository;
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
      $container->get('entity.repository'),
      $container->get('entity_display.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['view_mode'] = ['default' => 'default'];
    return $options;
  }
  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['view_mode'] = [
      '#type' => 'select',
      '#options' => $this->entityDisplayRepository->getViewModeOptions('user'),
      '#title' => $this->t('View mode'),
      '#default_value' => $this->options['view_mode'],
    ];
  }
  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE) {
    // We check if the views result are empty, or if the settings of this area
    // force showing this area even if the view is empty.
    if (!$empty || !empty($this->options['empty'])) {
      foreach ($this->view->argument as $arg) {
        if ($arg->getPluginId() != 'custom_user_slug') {
          continue;
        }
        if ($uid = $arg->getValue()) {
          /** @var \Drupal\user\Entity\UserInterface $user */
          $target_entity = User::load($uid);
          break;
        }
      }
      if (isset($target_entity)) {
        $view_builder = $this->entityTypeManager->getViewBuilder('user');
        return $view_builder->view($target_entity, $this->options['view_mode']);
      }
    }
    return array();
  }
}

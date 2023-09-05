<?php

namespace Drupal\mini_sites\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\DependencyInjection\DeprecatedServicePropertyTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\mini_sites\Entity\MiniSiteInterface;
use Drupal\mini_sites\Entity\MiniSitePageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Defines a generic controller to render a single entity.
 */
class MiniSiteViewController extends ControllerBase implements ContainerInjectionInterface {
  use DeprecatedServicePropertyTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Creates an EntityViewController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer) {
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('renderer')
    );
  }

  /**
   * Pre-render callback to build the page title.
   *
   * @param array $page
   *   A page render array.
   *
   * @return array
   *   The changed page render array.
   */
  public function buildTitle(array $page) {
    $entity_type = $page['#entity_type'];
    $entity = $page['#' . $entity_type];
    // If the entity's label is rendered using a field formatter, set the
    // rendered title field formatter as the page title instead of the default
    // plain text title. This allows attributes set on the field to propagate
    // correctly (e.g. RDFa, in-place editing).
    if ($entity instanceof FieldableEntityInterface) {
      $label_field = $entity->getEntityType()->getKey('label');
      if (isset($page[$label_field])) {
        $page['#title'] = $this->renderer->render($page[$label_field]);
      }
    }
    return $page;
  }

  /**
   * Provides a page to render a single entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $_entity
   *   The Entity to be rendered. Note this variable is named $_entity rather
   *   than $entity to prevent collisions with other named placeholders in the
   *   route.
   * @param string $view_mode
   *   (optional) The view mode that should be used to display the entity.
   *   Defaults to 'full'.
   *
   * @return array
   *   A render array as expected by
   *   \Drupal\Core\Render\RendererInterface::render().
   */
  public function view(MiniSiteInterface $mini_site, $view_mode = 'full') {
    $page = $this->entityTypeManager
      ->getViewBuilder($mini_site->getEntityTypeId())
      ->view($mini_site, $view_mode);

    $page['#pre_render'][] = [$this, 'buildTitle'];
    $page['#entity_type'] = $mini_site->getEntityTypeId();
    $page['#' . $page['#entity_type']] = $mini_site;

    return $page;
  }

}

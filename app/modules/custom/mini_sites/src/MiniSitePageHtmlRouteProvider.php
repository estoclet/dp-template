<?php

namespace Drupal\mini_sites;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Drupal\mini_sites\Controller\MiniSitePageController;
use Symfony\Component\Routing\Route;

/**
 * Provides routes for Mini site page entities.
 *
 * @see \Drupal\Core\Entity\Routing\AdminHtmlRouteProvider
 * @see \Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 */
class MiniSitePageHtmlRouteProvider extends AdminHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $collection = parent::getRoutes($entity_type);

    $entity_type_id = $entity_type->id();

    if ($settings_form_route = $this->getSettingsFormRoute($entity_type)) {
      $collection->add("$entity_type_id.settings", $settings_form_route);
    }

    return $collection;
  }

  /**
   * Gets the canonical route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getCanonicalRoute(EntityTypeInterface $entity_type) {
    if ($entity_type->hasLinkTemplate('canonical') && $entity_type->hasViewBuilderClass()) {
      $entity_type_id = $entity_type->id();
      $route = new Route($entity_type->getLinkTemplate('canonical'));
      $route
        ->addDefaults([
          '_controller' => '\Drupal\mini_sites\Controller\MiniSitePageViewController::view',
          '_title_callback' => '\Drupal\Core\Entity\Controller\EntityController::title',
        ])
        ->setRequirement('_entity_access', "{$entity_type_id}.view")
        ->setOption('parameters', [
          $entity_type_id => ['type' => 'entity:' . $entity_type_id],
        ]);

      // Entity types with serial IDs can specify this in their route
      // requirements, improving the matching process.
      if ($this->getEntityTypeIdKeyType($entity_type) === 'integer') {
        $route->setRequirement($entity_type_id, '\d+');
      }
      return $route;
    }
  }

  protected function getAddPageRoute(EntityTypeInterface $entity_type) {
    $route = parent::getAddPageRoute($entity_type);
    if ($entity_type->hasLinkTemplate('add-page') && $entity_type->getKey('bundle')) {
      $route->setDefault('_controller', MiniSitePageController::class . '::addPage');
    }
    return $route;
  }

  /**
   * Gets the settings form route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getSettingsFormRoute(EntityTypeInterface $entity_type) {
    if (!$entity_type->getBundleEntityType()) {
      $route = new Route("/admin/structure/{$entity_type->id()}/settings");
      $route
        ->setDefaults([
          '_form' => 'Drupal\mini_sites\Form\MiniSitePageSettingsForm',
          '_title' => "{$entity_type->getLabel()} settings",
        ])
        ->setRequirement('_permission', $entity_type->getAdminPermission())
        ->setOption('_admin_route', TRUE);

      return $route;
    }
  }

  /**
   * Gets the collection route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getCollectionRoute(EntityTypeInterface $entity_type) {
    // If the entity type does not provide an admin permission, there is no way
    // to control access, so we cannot provide a route in a sensible way.
    if ($entity_type->hasLinkTemplate('collection') && $entity_type->hasListBuilderClass() && ($admin_permission = $entity_type->getAdminPermission())) {
      /** @var \Drupal\Core\StringTranslation\TranslatableMarkup $label */
      $label = $entity_type->getCollectionLabel();

      $route = new Route($entity_type->getLinkTemplate('collection'));
      $route
        ->addDefaults([
          '_form' => 'Drupal\mini_sites\Form\OverviewMiniSitePage',
          '_title' => $label->getUntranslatedString(),
          '_title_arguments' => $label->getArguments(),
          '_title_context' => $label->getOption('context'),
        ])
        ->setOption('_admin_route', TRUE)
        ->setOption('parameter', [
          'mini_site' => ['type' => 'entity:mini_site']
        ])
        ->setRequirement('_permission', $admin_permission);
      return $route;
    }
  }

}

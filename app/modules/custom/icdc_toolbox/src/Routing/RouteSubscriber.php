<?php

namespace Drupal\icdc_toolbox\Routing;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
* Listens to the dynamic route events.
*/
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a route subscriber object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
  * {@inheritdoc}
  */
  protected function alterRoutes(RouteCollection $collection) {
    if(!$this->moduleHandler->moduleExists('admin_toolbar_tools')) {
      return;
    }

    $routes = [
      'admin_toolbar_tools.flush',
      'admin_toolbar_tools.cssjs',
      'admin_toolbar_tools.plugin',
      'admin_toolbar_tools.flush_static',
      'admin_toolbar_tools.flush_menu',
      'admin_toolbar_tools.flush_rendercache',
      'admin_toolbar_tools.flush_views',
      'admin_toolbar_tools.flush_twig'
    ];

    foreach($routes as $route_name) {
      // Change path '/user/login' to '/login'.
      if ($route = $collection->get($route_name)) {
        $route->setRequirement('_permission', 'icdc toolbox clear cache');
      }
    }
  }

}

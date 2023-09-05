<?php
namespace Drupal\apercu_view_mode\Controller;

use Drupal\Node\NodeInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

Class ApercuViewModeController extends ControllerBase{
  public function content(NodeInterface $node, string $viewMode){
    $nodeEntity = \Drupal::service('entity_display.repository');

    // call the necessary method in order to return 'node' view modes.
    $viewModeBundle = $nodeEntity->getViewModeOptionsByBundle('node', $node->getType());
    if(!array_key_exists($viewMode, $viewModeBundle)) {
      throw new NotFoundHttpException();
    }
    $view_builder = \Drupal::entityTypeManager()->getViewBuilder('node');
    return $view_builder->view($node, $viewMode);
  }
}

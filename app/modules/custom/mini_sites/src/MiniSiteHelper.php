<?php

namespace Drupal\mini_sites;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\mini_sites\Entity\MiniSite;
use Drupal\mini_sites\Entity\MiniSitePage;
use Drupal\node\NodeInterface;

/**
 * Helper for mini site page content.
 */
class MiniSiteHelper {

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  public function __construct(RouteMatchInterface $route_match)
  {
    $this->routeMatch = $route_match;
  }

  public function getCurrentMiniSite(&$miniSitePageEntity = NULL) {
    /**
     * @var \Drupal\mini_sites\Entity\MiniSite $miniSiteEntity
     */
    $miniSiteEntity = NULL;

    if(($node = $this->getNode()) && $node->hasField('field_site') && !empty($node->field_site->target_id)) {
      $miniSiteEntity = MiniSite::load($node->field_site->target_id);
      if(!empty($node->field_site_page->target_id)) {
        $miniSitePageEntity = MiniSitePage::load($node->field_site_page->target_id);
      }
    }
    elseif($miniSiteEntity = $this->getMiniSite()) {
      $miniSiteEntity = $miniSiteEntity;
    }

    if($miniSiteEntity && empty($miniSitePageEntity)) {
      $miniSitePageEntity = $this->routeMatch->getParameter('mini_site_page');
    }


    return $miniSiteEntity;
  }

  protected function getNode() {
    $obj = $this->routeMatch->getParameter('node');
    if (!$obj instanceof NodeInterface) {
      return FALSE;
    }
    return $obj;
  }

  protected function getMiniSite() {
    $obj = $this->routeMatch->getParameter('mini_site_page');
    if($obj instanceof MiniSitePage) {
      return $obj->getMiniSite();
    }

    $obj = $this->routeMatch->getParameter('mini_site');
    if ($obj instanceof MiniSite) {
      return $obj;
    }
    return FALSE;
  }
}

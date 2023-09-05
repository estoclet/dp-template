<?php

namespace Drupal\mini_sites;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines a Controller class for mini site pages.
 */
class MiniSiteStorage extends SqlContentEntityStorage {

  /**
   * {@inheritdoc}
   */
  public function getPages(EntityInterface $entity) {
    /** @var $pageStorage \Drupal\mini_sites\MiniSitePageStorage*/
    $pageStorage = $this->entityTypeManager->getStorage('mini_site_page');

    return $pageStorage->loadTree($entity->id());
  }

}

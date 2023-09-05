<?php

namespace Drupal\mini_sites;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Mini site page entities.
 *
 * @ingroup mini_sites
 */
class MiniSitePageListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Mini site page ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\mini_sites\Entity\MiniSitePage $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.mini_site_page.edit_form',
      ['mini_site_page' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}

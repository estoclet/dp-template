<?php

namespace Drupal\mini_sites\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Mini site page entities.
 */
class MiniSitePageViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}

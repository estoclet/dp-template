<?php

namespace Drupal\mini_sites;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\mini_sites\Entity\MiniSitePageType;


/**
 * Provides dynamic permissions for Mini site page of different types.
 *
 * @ingroup mini_sites
 *
 */
class MiniSitePagePermissions{

  use StringTranslationTrait;

  /**
   * Returns an array of node type permissions.
   *
   * @return array
   *   The MiniSitePage by bundle permissions.
   *   @see \Drupal\user\PermissionHandlerInterface::getPermissions()
   */
  public function generatePermissions() {
    $perms = [];

    foreach (MiniSitePageType::loadMultiple() as $type) {
      $perms += $this->buildPermissions($type);
    }

    return $perms;
  }

  /**
   * Returns a list of node permissions for a given node type.
   *
   * @param \Drupal\mini_sites\Entity\MiniSitePageType $type
   *   The MiniSitePage type.
   *
   * @return array
   *   An associative array of permission names and descriptions.
   */
  protected function buildPermissions(MiniSitePageType $type) {
    $type_id = $type->id();
    $type_params = ['%type_name' => $type->label()];

    return [
      "$type_id create entities" => [
        'title' => $this->t('Create new %type_name entities', $type_params),
      ],
      "$type_id edit own entities" => [
        'title' => $this->t('Edit own %type_name entities', $type_params),
      ],
      "$type_id edit any entities" => [
        'title' => $this->t('Edit any %type_name entities', $type_params),
      ],
      "$type_id delete own entities" => [
        'title' => $this->t('Delete own %type_name entities', $type_params),
      ],
      "$type_id delete any entities" => [
        'title' => $this->t('Delete any %type_name entities', $type_params),
      ],
    ];
  }

}

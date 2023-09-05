<?php

namespace Drupal\icdc_toolbox\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * An example controller.
 */
class VersionController extends ControllerBase {

  /**
   * Returns a render-able array for a test page.
   */
  public function content() {
    $version_conf = \Drupal::config('icdc_toolbox.settings');
    $version = '';
    $version_date = '';
    if ($version_conf) {
      $version = $version_conf->get('version', '');
      $version_date = $version_conf->get('version_date', '');
    }
    $build = [
      '#theme' => 'icdc_toolbox_version',
      '#version' => $version,
      '#version_date' => $version_date,
    ];
    return $build;
  }

}

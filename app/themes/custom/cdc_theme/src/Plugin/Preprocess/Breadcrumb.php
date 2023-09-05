<?php

namespace Drupal\cdc_theme\Plugin\Preprocess;

use Drupal\bootstrap\Plugin\Preprocess\Breadcrumb as BootstrapBreadcrumb;
use Drupal\bootstrap\Utility\Variables;

/**
 * Pre-processes variables for the "breadcrumb" theme hook.
 *
 * @ingroup plugins_preprocess
 *
 * @BootstrapPreprocess("breadcrumb")
 */
class Breadcrumb extends BootstrapBreadcrumb {

  /**
   * {@inheritdoc}
   */
  public function preprocessVariables(Variables $variables) {
    // Intentionally left blank to disable base theme's alterations.
  }

}

<?php

namespace Drupal\icdc_views_bootstrap;

use Drupal\views_bootstrap\ViewsBootstrap;

/**
 * The primary class for the Views Bootstrap module.
 *
 * Provides many helper methods.
 *
 * @ingroup utility
 */
class IcdcViewsBootstrap extends ViewsBootstrap {

  /**
   * Returns the theme hook definition information.
   */
  public static function getThemeHooks() {
    $hooks['icdc_views_bootstrap_grid_list'] = [
      'preprocess functions' => [
        'template_preprocess_icdc_views_bootstrap_grid_list',
      ],
      'file' => 'icdc_views_bootstrap.theme.inc',
    ];

    return $hooks;
  }

}

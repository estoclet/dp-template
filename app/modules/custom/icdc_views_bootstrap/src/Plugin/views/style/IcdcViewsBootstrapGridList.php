<?php

namespace Drupal\icdc_views_bootstrap\Plugin\views\style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;
use Drupal\icdc_views_bootstrap\IcdcViewsBootstrap;
use Drupal\views_bootstrap\Plugin\views\style\ViewsBootstrapGrid;

/**
 * Style plugin to render each item in an ordered or unordered list.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "icdc_views_bootstrap_grid_list",
 *   title = @Translation("ICDC Bootstrap Grid List"),
 *   help = @Translation("Displays rows in a Bootstrap Grid layout with ul li"),
 *   theme = "icdc_views_bootstrap_grid_list",
 *   theme_file = "../icdc_views_bootstrap.theme.inc",
 *   display_types = {"normal"}
 * )
 */
class IcdcViewsBootstrapGridList extends ViewsBootstrapGrid {

}

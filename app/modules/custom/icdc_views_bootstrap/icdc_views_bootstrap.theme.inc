<?php

/**
 * @file
 * Preprocessors and helper functions to make theming easier.
 */

use Drupal\icdc_views_bootstrap\IcdcViewsBootstrap;
use Drupal\Core\Template\Attribute;

/**
 * Prepares variables for views grid templates.
 *
 * Default template: views-bootstrap-grid.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - view: A ViewExecutable object.
 *   - rows: The raw row data.
 */
function template_preprocess_icdc_views_bootstrap_grid_list(array &$vars) {
  $view = $vars['view'];
  $vars['id'] = IcdcViewsBootstrap::getUniqueId($view);
  $vars['attributes']['class'][] = 'grid';
  $options = $view->style_plugin->options;

  $vars['row_attributes'] = new Attribute();
  foreach (IcdcViewsBootstrap::getBreakpoints() as $breakpoint) {
    if ($options["col_$breakpoint"] == 'none') {
      continue;
    }
    $vars['row_attributes']->addClass($options["col_$breakpoint"]);
  }

  $vars['options'] = $options;
}

<?php

/**
 * @file
 * Contains mini_site_page.page.inc.
 *
 * Page callback for Mini site page entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Mini site page templates.
 *
 * Default template: mini_site_page.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_mini_site_page(array &$variables) {
  // Fetch MiniSitePage Entity Object.
  $mini_site_page = $variables['elements']['#mini_site_page'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
  }

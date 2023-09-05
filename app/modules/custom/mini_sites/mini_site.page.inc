<?php

/**
 * @file
 * Contains mini_site.page.inc.
 *
 * Page callback for Mini site entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Mini site templates.
 *
 * Default template: mini_site.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_mini_site(array &$variables) {
  // Fetch MiniSite Entity Object.
  /**
   * @var $mini_site \Drupal\mini_sites\Entity\MiniSite
   */
  $mini_site = $variables['elements']['#mini_site'];
  $variables['url'] = $mini_site->url();
  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}

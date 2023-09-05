<?php

namespace Drupal\icdc_search\Plugin\views\field;

use Drupal\Core\Render\Markup;
use Drupal\search_api\Plugin\views\field\SearchApiStandard;

/**
 * Provides a default handler for fields in Search API Views.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("icdc_search_api_rendered_item")
 */
class IcdcSearchApiRenderedItem extends SearchApiStandard {

  /**
   * {@inheritdoc}
   */
  public function render_item($count, $item) {
    return Markup::create($item['value']);
  }

}

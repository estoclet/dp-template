<?php

namespace Drupal\icdc_facets\Plugin\search_api\processor\Property;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\search_api\IndexInterface;
use Drupal\search_api\Item\FieldInterface;
use Drupal\search_api\Processor\ConfigurablePropertyBase;
use Drupal\search_api\Processor\ConfigurablePropertyInterface;
use Drupal\search_api\Utility\Utility;

/**
 * Defines an "icdc facets date field" property.
 *
 * @see \Drupal\icdc_facets\Plugin\search_api\processor\IcdcFacetsDateRangeField
 */
class IcdcFacetsDateRangeFieldProperty extends IcdcFacetsDateFieldProperty {

  use StringTranslationTrait;

  protected function getAllowPropertyTypes() {
    return ['daterange'];
  }
}

<?php

namespace Drupal\icdc_facets\Plugin\search_api\processor;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\icdc_facets\Plugin\search_api\processor\Property\IcdcFacetsDateRangeFieldProperty;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Utility\Utility;

/**
 * Adds customized processing of existing date fields to the index.
 *
 * @see \Drupal\icdc_facets\Plugin\search_api\processor\Property\IcdcFacetsDateFieldProperty
 *
 * @SearchApiProcessor(
 *   id = "icdc_facets_date_range_field",
 *   label = @Translation("ICDC Facets Date Range field"),
 *   description = @Translation("Add customized processor for date range fields to the index."),
 *   stages = {
 *     "add_properties" = 20,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class IcdcFacetsDateRangeField extends IcdcFacetsDateField {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      $definition = [
        'label' => $this->t('ICDC Facets Date Range field'),
        'description' => $this->t('A preprocess for date range fields.'),
        'type' => 'string',
        'processor_id' => $this->getPluginId(),
        'is_list' => TRUE,
      ];
      $properties['icdc_facets_date_range_field'] = new IcdcFacetsDateRangeFieldProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $fields = $item->getFields(FALSE);
    $icdc_facets_date_range_fields = $this->getFieldsHelper()
      ->filterForPropertyPath($fields, NULL, 'icdc_facets_date_range_field');
    foreach ($icdc_facets_date_range_fields as $field) {
      $configuration = $field->getConfiguration();
      $fieldValues = $this->getFieldRangeValues($item, $configuration);
      foreach($fieldValues as $currentValue) {
        $field->addValue($currentValue);
      }
    }
  }

  public function getFieldRangeValues(ItemInterface $item, $configuration) {
    list($datasource_id, $property_path) = Utility::splitCombinedId($configuration['field']);
    $property_value = $this->getFieldsHelper()
      ->extractItemValues([$item], [$datasource_id=>[$property_path => 'start', $property_path . ':end_value' => 'end']])[0];
    $values = [];
    if (!empty($property_value['start'])) {
      foreach ($property_value['start'] as $index => $curentFieldValue) {
        $values[] = ['start' => $curentFieldValue, 'end' => $property_value['end'][$index]];
      }
    }
    $granularity = '';
    if(!empty($configuration['date_format'])) {
      $format = strtolower($configuration['date_format']);
      if(strpos($format, 'u') !== FALSE) {
        $granularity = 'u';
      }
      elseif(strpos($format, 's') !== FALSE) {
        $granularity = 's';
      }
      elseif(strpos($format, 'i') !== FALSE) {
        $granularity = 'i';
      }
      elseif(strpos($format, 'h') !== FALSE || strpos($format, 'g') !== FALSE) {
        $granularity = 'H';
      }
      elseif(strpos($format, 'd') !== FALSE || strpos($format, 'j') !== FALSE || strpos($format, 'l') !== FALSE || strpos($format, 'z') !== FALSE) {
        $granularity = 'l';
      }
      elseif(strpos($format, 'f') !== FALSE || strpos($format, 'm') !== FALSE || strpos($format, 'n') !== FALSE) {
        $granularity = 'F';
      }
      elseif(strpos($format, 'y') !== FALSE) {
        $granularity = 'Y';
      }
    }
    else {
      $granularity = $configuration['granularity'];
    }

    $dateInterval = NULL;
    switch($granularity) {
      case 'Y':
        $dateInterval = new \DateInterval('P1Y');
        break;
      case 'F':
        $dateInterval = new \DateInterval('P1M');
        break;
      case 'l':
        $dateInterval = new \DateInterval('P1D');
        break;
      case 'H':
        $dateInterval = new \DateInterval('P1H');
        break;
      case 'i':
        $dateInterval = new \DateInterval('P1I');
        break;
      case 's':
        $dateInterval = new \DateInterval('P1S');
        break;
      case 'u':
        $dateInterval = new \DateInterval('P1F');
        break;
      default:
        break;
    }

    $finalValues = [];
    foreach ($values as $curentFinalValue) {
      $finalValues[] = $this->formatValue($curentFinalValue['start'], $configuration['date_format'], $configuration['granularity']);
      if($dateInterval) {
        $dateCurrent = DrupalDateTime::createFromTimestamp($curentFinalValue['start']);
        $dateCurrent->add($dateInterval);
        $dateEnd = DrupalDateTime::createFromTimestamp($curentFinalValue['end']);

        while ($dateCurrent->getTimestamp() < $dateEnd->getTimestamp()) {
          $formattedValue = $this->formatValue($dateCurrent->getTimestamp(), $configuration['date_format'], $configuration['granularity']);
          if(!in_array($formattedValue, $finalValues)) {
            $finalValues[] = $formattedValue;
          }
          $dateCurrent->add($dateInterval);
        }
      }
      $endFormatted = $this->formatValue($curentFinalValue['end'], $configuration['date_format'], $configuration['granularity']);
      if(!in_array($endFormatted, $finalValues)) {
        $finalValues[] = $endFormatted;
      }
    }
    return $finalValues;
  }
}


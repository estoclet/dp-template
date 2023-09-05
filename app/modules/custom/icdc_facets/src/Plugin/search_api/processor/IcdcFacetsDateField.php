<?php

namespace Drupal\icdc_facets\Plugin\search_api\processor;

use Drupal\Core\Datetime\DateFormatter;
use Drupal\icdc_facets\Plugin\search_api\processor\Property\IcdcFacetsDateFieldProperty;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Utility\Utility;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Adds customized processing of existing date fields to the index.
 *
 * @see \Drupal\icdc_facets\Plugin\search_api\processor\Property\IcdcFacetsDateFieldProperty
 *
 * @SearchApiProcessor(
 *   id = "icdc_facets_date_field",
 *   label = @Translation("ICDC Facets Date field"),
 *   description = @Translation("Add customized processor for date fields to the index."),
 *   stages = {
 *     "add_properties" = 20,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class IcdcFacetsDateField extends ProcessorPluginBase {

  /**
   * Date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var static $processor */
    $processor = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $processor->setDateFormatter($container->get('date.formatter'));
    return $processor;
  }

  /**
   * @return \Drupal\Core\Datetime\DateFormatter
   */
  public function getDateFormatter() {
    return $this->dateFormatter;
  }

  /**
   * @param \Drupal\Core\Datetime\DateFormatter $dateFormatter
   */
  public function setDateFormatter(DateFormatter $dateFormatter) {
    $this->dateFormatter = $dateFormatter;
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      $definition = [
        'label' => $this->t('ICDC Facets Date field'),
        'description' => $this->t('A preprocess for date fields.'),
        'type' => 'string',
        'processor_id' => $this->getPluginId(),
        'is_list' => FALSE,
      ];
      $properties['icdc_facets_date_field'] = new IcdcFacetsDateFieldProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $fields = $item->getFields(FALSE);
    $icdc_facets_date_fields = $this->getFieldsHelper()
      ->filterForPropertyPath($fields, NULL, 'icdc_facets_date_field');
    foreach ($icdc_facets_date_fields as $field) {
      $configuration = $field->getConfiguration();
      $fieldValues = $this->getFieldValues($item, $configuration);
      foreach($fieldValues as $currentValue) {
        $field->addValue(
          $this->formatValue($currentValue, $configuration['date_format'], $configuration['granularity'])
        );
      }
    }
  }

  public function getFieldValues(ItemInterface $item, $configuration) {
    list($datasource_id, $property_path) = Utility::splitCombinedId($configuration['field']);
    $property_value = $this->getFieldsHelper()
      ->extractItemValues([$item], [$datasource_id=>[$property_path => 'value']])[0];
    if (!empty($property_value['value'])) {
      return $property_value['value'];
    }
    return [];
  }

  protected function formatValue($value, $date_format,$granularity) {
    $format = !empty($date_format) ? $date_format : $granularity;
    $value = $this->getDateFormatter()->format($value, 'custom', $format);
    switch($format) {
      case 'F':
      case 'l':
        $value = $this->t($value);
        break;
      case 'H':
        $value .= 'h';
        break;
      case 'i':
        $value .= 'm';
        break;
      case 's':
        $value .= 's';
        break;
      default:
        break;
    }
    return $value;
  }
}


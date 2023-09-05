<?php

namespace Drupal\icdc_mediatheque\Plugin\search_api\processor;

use Drupal\Core\Datetime\DateFormatter;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Adds the item's Year to the indexed data.
 *
 * @SearchApiProcessor(
 *   id = "media_facet_year",
 *   label = @Translation("ICDC Media facet year"),
 *   description = @Translation("Adds the item's year to the indexed data."),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class MediaFacetYear extends ProcessorPluginBase {

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
        'label' => $this->t('ICDC Media Facet Year'),
        'description' => $this->t('A Year value'),
        'type' => 'string',
        'processor_id' => $this->getPluginId(),
      ];
      $properties['media_facet_year'] = new ProcessorProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    /**
     * @var $entity \Drupal\Core\Entity\ContentEntityBase
     */
    $entity = $item->getOriginalObject()->getValue();
    if ($entity) {
      $year = '';
      if($entity->hasField('field_media_date')) {
        if(!$entity->field_media_date->isEmpty()) {
          $year = $this->getDateFormatter()->format($entity->field_media_date->date->getTimestamp(), 'custom', 'Y');
        }
      }
      else if($entity->hasField('field_media_year')) {
        if(!$entity->field_media_year->isEmpty()) {
          $year = $this->getDateFormatter()->format($entity->field_media_year->date->getTimestamp(), 'custom', 'Y');
        }
      }
      if(!empty($year)) {
        $fields = $item->getFields(FALSE);
        $fields = $this->getFieldsHelper()
          ->filterForPropertyPath($fields, NULL, 'media_facet_year');
        foreach ($fields as $field) {
          $field->addValue($year);
        }
      }
    }
  }

}

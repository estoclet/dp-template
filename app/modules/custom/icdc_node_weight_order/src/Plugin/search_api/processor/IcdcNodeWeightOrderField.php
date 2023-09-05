<?php

namespace Drupal\icdc_node_weight_order\Plugin\search_api\processor;

use Drupal\elasticsearch_connector\Plugin\search_api\backend\SearchApiElasticsearchBackend;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\IndexInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;
use Drupal\search_api\Query\QueryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Adds the item's URL to the indexed data.
 *
 * @SearchApiProcessor(
 *   id = "icdc_node_order",
 *   label = @Translation("ICDC Node Weight Order field"),
 *   description = @Translation("Adds the item's order data."),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class IcdcNodeWeightOrderField extends ProcessorPluginBase {

  /**
   * The manager.
   *
   * @var \Drupal\icdc_node_weight_order\IcdcNodeWeightManager
   */
  protected $manager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var static $processor */
    $processor = parent::create($container, $configuration, $plugin_id, $plugin_definition);

    $processor->setManager($container->get('icdc_node_weight_order.node_weight_manager'));

    return $processor;
  }

  /**
   * @return \Drupal\icdc_node_weight_order\IcdcNodeWeightManager
   */
  public function getManager() {
    return $this->manager;
  }

  /**
   * @param \Drupal\icdc_node_weight_order\IcdcNodeWeightManager $manager
   */
  public function setManager($manager) {
    $this->manager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      $definition = [
        'label' => $this->t('ICDC Node Weight Order field'),
        'description' => $this->t('A ICDC Node Weight Order field'),
        'type' => 'object',
        'processor_id' => $this->getPluginId(),
      ];
      $properties['icdc_node_weight_order_field'] = new ProcessorProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $itemId = $item->getDatasource()->getItemId($item->getOriginalObject());
    if($itemId) {
      list($id, $lang) = explode(':', $itemId);

      $fields = $item->getFields(FALSE);
      $fields = $this->getFieldsHelper()
        ->filterForPropertyPath($fields, NULL, 'icdc_node_weight_order_field');

      $values = $this->manager->getNodesOrderByNid($id);
      if(!empty($values)) {
        foreach ($fields as $field) {
          $field->addValue($values);
        }
      }
    }
  }
}

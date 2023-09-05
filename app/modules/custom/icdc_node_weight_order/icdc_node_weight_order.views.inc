<?php

/**
 * @file
 * Provide views data for entity_browser.module.
 */

use Drupal\search_api\Entity\Index;

/**
 * Implements hook_views_data_alter().
 */
function icdc_node_weight_order_views_data_alter(&$data) {
  $indexes = Index::loadMultiple();
  foreach($indexes as $index) {
    $fields = $index->getFields(FALSE);
    $fields = \Drupal::service('search_api.fields_helper')
      ->filterForPropertyPath($fields, NULL, 'icdc_node_weight_order_field');

    foreach($fields as $fieldId => $field) {
      if(!empty($data['search_api_index_' . $index->id()][$fieldId])) {
        $data['search_api_index_' . $index->id()][$fieldId]['sort'] = [
          'id' => 'icdc_node_weight_order',
          'real field' => $data['search_api_index_' . $index->id()][$fieldId]['field']['real field'],
          'no group by' => TRUE,
        ];
        $data['search_api_index_' . $index->id()][$fieldId]['filter'] = [
          'id' => 'icdc_node_weight_filter',
          'allow empty' => FALSE,
          'real field' => $data['search_api_index_' . $index->id()][$fieldId]['field']['real field']
        ];
        unset($data['search_api_index_' . $index->id()][$fieldId]['field']);
      }
    }
  }
}

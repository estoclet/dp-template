<?php

namespace Drupal\icdc_node_weight_order\Plugin\views\sort;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\sort\SortPluginBase;

/**
 * icdc_node_weight_order.
 *
 * @ViewsSort("icdc_node_weight_order")
 */
class IcdcNodeWeightOrder extends SortPluginBase {

  /**
   * The associated views query object.
   *
   * @var \Drupal\search_api\Plugin\views\query\SearchApiQuery
   */
  public $query;

  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['input_filter'] = [
      'default' => NULL,
    ];
    return $options;
  }

  /**
   * Basic options for all sort criteria
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $filters = ['' => $this->t('Choose')];
    foreach($this->view->getHandlers('filter') as $filterId => $filterConfig) {
      if($filterConfig['exposed']) {
        $filters[$filterConfig['expose']['identifier']] = $filterConfig['expose']['label'];
      }
    }

    $form['input_filter'] = [
      '#type' => 'select',
      '#title' => $this->t('Input exposed filter'),
      '#options' => $filters,
      '#description' => $this->t('Input filter for nested sort param.'),
      '#default_value' => $this->options['input_filter'],
      '#required' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $searchValue = '';
    $viewsInputs = $this->view->getExposedInput();
    if(!empty($viewsInputs[$this->options['input_filter']])) {
      $searchValue = $viewsInputs[$this->options['input_filter']];
    }

    $options = $this->query->getOption('icdc_node_weight_order', [
      'fields' => [],
      'search_value' => $searchValue
    ]);
    $this->query->sort($this->realField, $this->options['order']);
    $options['fields'][] = $this->realField;
    $this->query->setOption('icdc_node_weight_order', $options);
  }
}

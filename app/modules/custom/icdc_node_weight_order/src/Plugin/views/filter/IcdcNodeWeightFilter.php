<?php

namespace Drupal\icdc_node_weight_order\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\FilterPluginBase;

/**
 * icdc_node_weight_filter.
 *
 * @ViewsFilter("icdc_node_weight_filter")
 */
class IcdcNodeWeightFilter extends FilterPluginBase {

  /**
   * Where the $query object will reside:
   *
   * @var \Drupal\search_api\Plugin\views\query\SearchApiQuery
   */
  public $query = NULL;

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

    if(!empty($searchValue)) {
      $options = $this->query->getOption('icdc_node_weight_filter', [
        'fields' => [],
        'search_value' => $searchValue,
        'input_filter' => $this->options['input_filter']
      ]);

      $options['fields'][] = $this->realField;
//      $this->query->addWhere($this->options['group'], $this->realField, $searchValue, '=');

      $this->query->setOption('icdc_node_weight_filter', $options);
    }
  }
}

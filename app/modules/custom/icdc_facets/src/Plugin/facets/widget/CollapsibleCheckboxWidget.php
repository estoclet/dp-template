<?php

namespace Drupal\icdc_facets\Plugin\facets\widget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\facets\FacetInterface;
use Drupal\facets\Result\ResultInterface;
use Drupal\facets\Widget\WidgetPluginBase;

/**
 * The checkbox / radios widget.
 *
 * @FacetsWidget(
 *   id = "collapsible_checkbox",
 *   label = @Translation("List of checkboxes inside a collapsible panel"),
 *   description = @Translation("A configurable widget that shows a list of checkboxes inside a collapsible panel"),
 * )
 */
class CollapsibleCheckboxWidget extends WidgetPluginBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
        'collapsible_title' => '',
      ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet) {
    $urlProcessorManager = \Drupal::service('plugin.manager.facets.url_processor');
    $url_processor = $urlProcessorManager->createInstance($facet->getFacetSourceConfig()->getUrlProcessorName(), ['facet' => $facet]);
    $active_filters = $url_processor->getActiveFilters();
    $empty_filters = [];

    $params = [];
    array_walk($active_filters, function($values, $facet) use(&$params, &$empty_filters) {
      if(!isset($empty_filters[$facet])) {
        $empty_filters[$facet] = [];
      }
      foreach($values as $currentValue) {
        $params[] = $facet . ':' . $currentValue;
      }
    });

    $request = \Drupal::request();
    $url = Url::createFromRequest($request);
    $urlParams = $request->query->all();
    unset($urlParams[$url_processor->getFilterKey()]);
    unset($urlParams['page']);
    $url->setOption('query', $urlParams);

    return [
      '#title' => $this->getConfiguration()['collapsible_title'],
      '#attached' => [
        'library' => ['icdc_facets/drupal.facets.collapsible-checkbox-widget'],
        'drupalSettings' => [
          'facets' => [
            $facet->id() => [
              'baseUrl' => $url->toString(),
              'urlParams' => $params
            ]
          ]
        ]
      ]
    ] + parent::build($facet);
  }

  /**
   * Builds a facet result item.
   *
   * @param \Drupal\facets\Result\ResultInterface $result
   *   The result item.
   *
   * @return array
   *   The facet result item as a render array.
   */
  protected function buildResultItem(ResultInterface $result) {
    $count = $result->getCount();
    return [
      '#theme' => 'icdc_facets_result_item',
      '#is_active' => $result->isActive(),
      '#value' => $result->getDisplayValue(),
      '#show_count' => $this->getConfiguration()['show_numbers'] && ($count !== NULL),
      '#count' => $count,
      '#facet' => $result->getFacet(),
      '#raw_value' => $result->getRawValue(),
    ];
  }

  /**
   * Returns the text or link for an item.
   *
   * @param \Drupal\facets\Result\ResultInterface $result
   *   A result item.
   *
   * @return array
   *   The item as a render array.
   */
  protected function prepareLink(ResultInterface $result) {
    return $this->buildResultItem($result);
  }

  /**
   * Provides a full array of possible theme functions to try for a given hook.
   *
   * This allows the following template suggestions:
   *  - facets-item-list--WIDGET_TYPE--FACET_ID
   *  - facets-item-list--WIDGET_TYPE
   *  - facets-item-list.
   *
   * @param \Drupal\facets\FacetInterface $facet
   *   The facet whose output is being generated.
   *
   * @return string
   *   A theme hook name with suggestions, suitable for the #theme property.
   */
  protected function getFacetItemListThemeHook(FacetInterface $facet) {
    return 'icdc_facets_item_list__' . $facet->getWidget()['type'] . '__' . $facet->id();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet) {
    $form = parent::buildConfigurationForm($form, $form_state, $facet);

    $form['collapsible_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Collapsible title'),
      '#description' => $this->t('This text will be used for collapsible container title.'),
      '#default_value' => $this->getConfiguration()['collapsible_title']
    ];
    return $form;
  }

}

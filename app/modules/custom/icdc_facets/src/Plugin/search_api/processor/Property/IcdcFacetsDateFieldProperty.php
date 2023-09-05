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
 * @see \Drupal\icdc_facets\Plugin\search_api\processor\IcdcFacetsDateField
 */
class IcdcFacetsDateFieldProperty extends ConfigurablePropertyBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'field' => '',
      'granularity' => 'Y',
      'date_format' => ''
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(FieldInterface $field, array $form, FormStateInterface $form_state) {
    $index = $field->getIndex();
    $configuration = $field->getConfiguration();

    $form['#attached']['library'][] = 'search_api/drupal.search_api.admin_css';
    $form['#tree'] = TRUE;

    $form['field'] = [
      '#type' => 'select',
      '#title' => $this->t('Field'),
      '#options' => [],
      '#attributes' => ['class' => ['search-api-select-list']],
      '#default_value' => $configuration['field'],
      '#required' => TRUE,
      '#weight' => 0
    ];
    $datasource_labels = $this->getDatasourceLabelPrefixes($index);
    $properties = $this->getAvailableProperties($index);
    $field_options = [];
    foreach ($properties as $combined_id => $property) {
      list($datasource_id, $name) = Utility::splitCombinedId($combined_id);

      $label = $datasource_labels[$datasource_id] . $property->getLabel();
      $field_options[$combined_id] = Utility::escapeHtml($label);
      if ($property instanceof ConfigurablePropertyInterface) {
        $description = $property->getFieldDescription($field);
      }
      else {
        $description = $property->getDescription();
      }
      $form['field'][$combined_id] = [
        '#attributes' => ['title' => $this->t('Machine name: @name', ['@name' => $name])],
        '#description' => $description,
      ];
    }
    // Set the field options in a way that sorts them first by whether they are
    // selected (to quickly see which one are included) and second by their
    // labels.
    asort($field_options, SORT_NATURAL);
    $selected = [$configuration['field']];
    $form['field']['#options'] = array_intersect_key($field_options, $selected);
    $form['field']['#options'] += array_diff_key($field_options, $selected);

    $form['granularity'] = [
      '#type' => 'radios',
      '#title' => $this->t('Granularity'),
      '#default_value' => $configuration['granularity'],
      '#options' => $this->granularityOptions(),
      '#weight' => 2
    ];

    $form['date_format'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date format'),
      '#default_value' => $configuration['date_format'],
      '#description' => $this->t('Override default date format used for the displayed filter format. See the <a href="http://php.net/manual/function.date.php">PHP manual</a> for available options.'),
      '#states' => [
        'visible' => [':input[name="facet_settings[date_item][settings][date_display]"]' => ['value' => 'actual_date']],
      ],
      '#weight' => 3
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(FieldInterface $field, array &$form, FormStateInterface $form_state) {
    $values = $this->buildSubmitValues($form, $form_state);
    $field->setConfiguration($values);
  }

  protected function buildSubmitValues(array &$form, FormStateInterface $form_state) {
    return [
      'field' => $form_state->getValue('field'),
      'granularity' => $form_state->getValue('granularity'),
      'date_format' => $form_state->getValue('date_format'),
    ];
  }

  /**
   * Human readable array of granularity options.
   *
   * @return array
   *   An array of granularity options.
   */
  private function granularityOptions() {
    return [
      'Y' => $this->t('Year'),
      'F' => $this->t('Month'),
      'l' => $this->t('Day'),
      'H' => $this->t('Hour'),
      'i' => $this->t('Minute'),
      's' => $this->t('Second', [], ['context' => 'timeperiod']),
    ];
  }

  /**
   * Retrieves label prefixes for an index's datasources.
   *
   * @param \Drupal\search_api\IndexInterface $index
   *   The search index.
   *
   * @return string[]
   *   An associative array mapping datasource IDs (and an empty string for
   *   datasource-independent properties) to their label prefixes.
   */
  protected function getDatasourceLabelPrefixes(IndexInterface $index) {
    $prefixes = [];
    foreach ($index->getDatasources() as $datasource_id => $datasource) {
      $prefixes[$datasource_id] = $datasource->label() . ' » ';
    }

    return $prefixes;
  }

  /**
   * Retrieve all properties available on the index.
   *
   * The properties will be keyed by combined ID, which is a combination of the
   * datasource ID and the property path. This is used internally in this class
   * to easily identify any property on the index.
   *
   * @param \Drupal\search_api\IndexInterface $index
   *   The search index.
   *
   * @return \Drupal\Core\TypedData\DataDefinitionInterface[]
   *   All the properties available on the index, keyed by combined ID.
   *
   * @see \Drupal\search_api\Utility::createCombinedId()
   */
  protected function getAvailableProperties(IndexInterface $index) {
    $properties = [];

    $datasource_ids = $index->getDatasourceIds();
    foreach ($datasource_ids as $datasource_id) {
      foreach ($index->getPropertyDefinitions($datasource_id) as $property_path => $property) {
        if(in_array($property->getType(), $this->getAllowPropertyTypes()))  {
          $properties[Utility::createCombinedId($datasource_id, $property_path)] = $property;
        }
      }
    }

    return $properties;
  }

  protected function getAllowPropertyTypes() {
    return ['created', 'changed', 'datetime'];
  }
}

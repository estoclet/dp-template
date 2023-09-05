<?php

namespace Drupal\mini_sites\Plugin\Field\FieldType;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\node\Entity\NodeType;
use Drupal\views\Entity\View;
use Drupal\views\Views;

/**
 * Plugin implementation of the 'mini_site_view' field type.
 *
 * @FieldType(
 *   id = "mini_site_view",
 *   label = @Translation("List mini_site content"),
 *   description = @Translation("A field to render a list of mini_site content"),
 *   category = @Translation("Mini Site"),
 *   default_widget = "mini_site_view_widget",
 *   default_formatter = "mini_site_view_formatter",
 * )
 */
class MiniSiteViewItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
        'view_id' => 'mini_site',
        'view_display' => 'page_1',
        'view_target_bundle' => NULL
      ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['view_id'] = DataDefinition::create('string')
      ->setLabel(t('View'))
      ->setRequired(TRUE);
    $properties['view_display'] = DataDefinition::create('string')
      ->setLabel(t('Display'))
      ->setRequired(TRUE);
    $properties['view_target_bundle'] = DataDefinition::create('string')
      ->setLabel(t('Type'))
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'view_id' => [
          'type' => 'varchar',
          'length' => 255,
        ],
        'view_display' => [
          'type' => 'varchar',
          'length' => 255,
        ],
        'view_target_bundle' => [
          'type' => 'varchar',
          'length' => 255,
        ]
      ],
      'indexes' => [
        'view_id' => ['view_id'],
        'view_target_bundle' => ['view_target_bundle'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $field = $form_state->getFormObject()->getEntity();
    $element = [
      '#type' => 'container',
      '#process' => [[get_class($this), 'fieldSettingsAjaxProcess']],
      '#element_validate' => [[get_class($this), 'fieldSettingsFormValidate']],
    ];

    $viewsDisplayOptions = Views::getViewsAsOptions(TRUE, 'enabled', NULL, FALSE, TRUE);
    $element['view_id'] = [
      '#type' => 'select',
      '#title' => $this->t('View'),
      '#options' => $viewsDisplayOptions,
      '#default_value' => $field->getSetting('view_id'),
      '#ajax' => TRUE,
      '#required' => TRUE,
    ];


    if($field->getSetting('view_id') && ($view = View::load($field->getSetting('view_id')))) {
      $displays = $view->get('display');
      unset($displays['default']);
      foreach($displays as $display_id => $display) {
        $displayOptions[$display_id] = $display['display_title'];
      }
    }

    $element['view_display'] = [
      '#type' => 'select',
      '#title' => $this->t('Display'),
      '#options' => $displayOptions,
      '#default_value' => $field->getSetting('view_display'),
      '#required' => TRUE,
    ];

    $node_types_enabled = \Drupal::config('mini_sites.settings')->get('node_type_field_site.node_types') ?: [];
    $type_options = array_map(function (NodeType $nodeType) { return $nodeType->label(); }, NodeType::loadMultiple());
    $type_options = array_intersect_key($type_options, $node_types_enabled);

    $element['view_target_bundle'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#options' => $type_options,
      '#default_value' => $field->getSetting('view_target_bundle'),
      '#required' => TRUE,
    ];

    return $element;
  }

  /**
   * Render API callback: Processes the field settings form and allows access to
   * the form state.
   *
   * @see static::fieldSettingsForm()
   */
  public static function fieldSettingsAjaxProcess($form, FormStateInterface $form_state) {
    static::fieldSettingsAjaxProcessElement($form, $form);
    return $form;
  }

  /**
   * Adds entity_reference specific properties to AJAX form elements from the
   * field settings form.
   *
   * @see static::fieldSettingsAjaxProcess()
   */
  public static function fieldSettingsAjaxProcessElement(&$element, $main_form) {
    if (!empty($element['#ajax'])) {
      $element['#ajax'] = [
        'callback' => [get_called_class(), 'settingsAjax'],
        'wrapper' => $main_form['#id'],
        'element' => $main_form['#array_parents'],
      ];
    }

    foreach (Element::children($element) as $key) {
      static::fieldSettingsAjaxProcessElement($element[$key], $main_form);
    }
  }

  /**
   * Ajax callback for the handler settings form.
   *
   * @see static::fieldSettingsForm()
   */
  public static function settingsAjax($form, FormStateInterface $form_state) {
    return NestedArray::getValue($form, $form_state->getTriggeringElement()['#ajax']['element']);
  }

  /**
   * Form element validation handler; Invokes selection plugin's validation.
   *
   * @param array $form
   *   The form where the settings form is being included in.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the (entire) configuration form.
   */
  public static function fieldSettingsFormValidate(array $form, FormStateInterface $form_state) {
    $values = $form_state->getValue('settings');
    if(!($view = View::load($values['view_id'])) || empty($view->get('display')[$values['view_display']])) {
      $form_state->setValue(['settings', 'view_id'], 'mini_site');
      $form_state->setValue(['settings', 'view_display'], 'page_1');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $nodeType = array_shift(NodeType::loadMultiple());
    $values = [
      'view_id' => 'mini_site',
      'view_display' => 'default',
      'view_target_bundle' => $nodeType->id()
    ];
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $view_id = $this->get('view_id')->getValue();
    $view_display = $this->get('view_display')->getValue();
    $view_target_bundle = $this->get('view_target_bundle')->getValue();
    return empty($view_id) || empty($view_display) || empty($view_target_bundle);
  }

}

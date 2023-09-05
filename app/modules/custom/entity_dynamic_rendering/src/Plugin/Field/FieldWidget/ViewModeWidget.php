<?php

namespace Drupal\entity_dynamic_rendering\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'view_mode_widget' widget.
 *
 * @FieldWidget(
 *   id = "view_mode_widget",
 *   label = @Translation("View Mode widget"),
 *   field_types = {
 *     "view_mode_item"
 *   }
 * )
 */
class ViewModeWidget extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * The display repository service.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $displayRepository;

  /**
   * List of available view modes.
   */
  protected $viewModes = [];

  /**
   * Constructs widget plugin.
   *
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $display_repository
   *   The entity display repository service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityDisplayRepositoryInterface $display_repository) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $field_settings = $field_definition->getSettings();
    $entity_type = $field_definition->getTargetEntityTypeId();
    $bundle = $field_definition->getTargetBundle();
    $this->displayRepository = $display_repository;

    // Get all view modes for the current bundle.
    $view_modes = $this->displayRepository->getViewModeOptionsByBundle($entity_type, $bundle);

    // Reduce options by enabled view modes
    foreach (array_keys($view_modes) as $view_mode) {
      if(isset($field_settings['view_modes'][$view_mode]['enable']) && $field_settings['view_modes'][$view_mode]['enable']) {
        continue;
      }
      unset($view_modes[$view_mode]);
    }

    // Show all view modes in widget when no view modes are enabled.
    if (!count($view_modes)) {
      $view_modes = $this->displayRepository->getViewModeOptionsByBundle($entity_type, $bundle);
    }

    $this->viewModes = $view_modes;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('entity_display.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'restrict_view_modes' => [],
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];
    $elements['restrict_view_modes'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Restrict view mode to'),
      '#default_value' => $this->getSetting('restrict_view_modes'),
      '#options' => $this->viewModes,
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $restrict_view_modes = $this->getSetting('restrict_view_modes');
    if(!empty($restrict_view_modes)) {
      $summary = [];
      $view_modes = [];
      foreach($restrict_view_modes as $key => $value) {
        if($value !== 0) {
          $view_modes[] = $this->viewModes[$key];
        }
      }
    }
    else {
      $view_modes[] = $this->t('All');
    }

    $summary[] = $this->t('Restrict view mode to: @restrict_view_modes', [
      '@restrict_view_modes' => implode(', ', $view_modes)
    ]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $restrict_view_modes = $this->getSetting('restrict_view_modes');
    $view_modes = $this->viewModes;
    if(!empty($restrict_view_modes)) {
      $view_modes = [];
      foreach($restrict_view_modes as $key => $value) {
        if($value !== 0) {
          $view_modes[$key] = $this->viewModes[$key];
        }
      }
    }

    $element['value'] = $element + [
      '#type' => 'select',
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
      '#options' => $view_modes,
      '#empty_option' => $this->t('- None -')
    ];

    return $element;
  }

}

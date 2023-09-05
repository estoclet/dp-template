<?php

namespace Drupal\mini_sites\Plugin\Field\FieldType;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\TypedData\EntityDataDefinition;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Field\PreconfiguredFieldUiOptionsInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\OptGroup;
use Drupal\Core\Render\Element;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataReferenceDefinition;
use Drupal\Core\TypedData\DataReferenceTargetDefinition;
use Drupal\Core\TypedData\OptionsProviderInterface;
use Drupal\Core\Validation\Plugin\Validation\Constraint\AllowedValuesConstraint;

/**
 * Defines the 'entity_reference' entity field type.
 *
 * Supported settings (below the definition's 'settings' key) are:
 * - target_type: The entity type to reference. Required.
 *
 * @FieldType(
 *   id = "mini_site_entity_reference",
 *   label = @Translation("Mini site Content"),
 *   description = @Translation("An node field containing an reference."),
 *   category = @Translation("Mini Site"),
 *   default_widget = "mini_site_entity_reference_autocomplete",
 *   default_formatter = "mini_site_entity_reference_entity_view",
 *   list_class = "\Drupal\Core\Field\EntityReferenceFieldItemList",
 * )
 */
class MiniSiteEntityReferenceItem extends EntityReferenceItem {
  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    $settings = parent::defaultStorageSettings();
    $settings['target_type'] = 'node';
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
        'handler' => 'mini_site_content',
        'handler_settings' => [],
      ] + parent::defaultFieldSettings();
  }


  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element['target_type'] = [
      '#type' => 'value',
      '#value' => 'node'
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::fieldSettingsForm($form, $form_state);
    $field = $form_state->getFormObject()->getEntity();
    $form['handler']['handler'] = [
      '#type' => 'value',
      '#value' => $field->getSetting('handler')
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public static function getPreconfiguredOptions() {
    return [];
  }
}

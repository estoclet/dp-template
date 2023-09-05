<?php

namespace Drupal\media_entity_soundcloud_formatter_pro\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\media_entity_soundcloud\Plugin\media\Source\Soundcloud;

/**
 * Plugin implementation of the 'soundcloud_embed_pro' formatter.
 *
 * @FieldFormatter(
 *   id = "soundcloud_embed_pro",
 *   label = @Translation("Soundcloud embed pro"),
 *   field_types = {
 *     "link", "string", "string_long"
 *   }
 * )
 */
class SoundcloudEmbedProFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'color' => '',
      'width' => '100%',
      'height' => '450',
      'options' => [],
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['color'] = [
      '#title' => $this->t('Color'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('color'),
      '#description' => $this->t('Color.'),
    ];

    $elements['width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Width'),
      '#default_value' => $this->getSetting('width'),
      '#min' => 1,
      '#required' => TRUE,
      '#description' => $this->t('Width of embedded player.'),
    ];

    $elements['height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Height'),
      '#default_value' => $this->getSetting('height'),
      '#min' => 1,
      '#required' => TRUE,
      '#description' => $this->t('Height (px) of embedded player. Suggested values: 450 for the visual type and 166 for classic.'),
    ];

    $elements['options'] = [
      '#title' => $this->t('Options'),
      '#type' => 'checkboxes',
      '#default_value' => $this->getSetting('options'),
      '#options' => $this->getEmbedOptions(),
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [
      $this->t('Color: @color', [
        '@color' => $this->getSetting('color'),
      ]),
      $this->t('Width: @width', [
        '@width' => $this->getSetting('width'),
      ]),
      $this->t('Height: @height', [
        '@height' => $this->getSetting('height'),
      ]),
    ];
    $options = $this->getSetting('options');
    if (count($options)) {
      $summary[] = $this->t('Options: @options', [
        '@options' => implode(', ', array_intersect_key($this->getEmbedOptions(), array_flip($this->getSetting('options')))),
      ]);
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    /** @var \Drupal\media\MediaInterface $media */
    $media = $items->getEntity();

    $element = [];
    if (($source = $media->getSource()) && $source instanceof Soundcloud) {
      /** @var \Drupal\media\MediaTypeInterface $item */
      foreach ($items as $delta => $item) {
        if ($source_id = $source->getMetadata($media, 'source_id')) {
          $element[$delta] = [
            '#theme' => 'media_soundcloud_embed_pro',
            '#source_id' => $source_id,
            '#width' => $this->getSetting('width'),
            '#height' => $this->getSetting('height'),
            '#color' => $this->getSetting('color'),
            '#options' => $this->getSetting('options'),
          ];
        }
      }
    }
    return $element;
  }

  /**
   * Returns an array of options for the embedded player.
   *
   * @return array
   *   An array of options for the embedded player.
   */
  protected function getEmbedOptions() {
    return [
      'inverse' => $this->t('Inverse'),
      'auto_play' => $this->t('Autoplay'),
      'hide_related' => $this->t('Hide related'),
      'show_comments' => $this->t('Show comments'),
      'show_user' => $this->t('Show user'),
      'show_reposts' => $this->t('Show reposts'),
    ];
  }

}

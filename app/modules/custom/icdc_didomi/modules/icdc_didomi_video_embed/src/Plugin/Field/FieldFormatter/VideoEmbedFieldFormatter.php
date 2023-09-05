<?php

namespace Drupal\icdc_didomi_video_embed\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\video_embed_field\Plugin\Field\FieldFormatter\Video;

/**
 * Plugin implementation of the 'video_embed_field_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "video_embed_field_formatter",
 *   label = @Translation("ICDC Didomi Video"),
 *   field_types = {
 *     "video_embed_field"
 *   }
 * )
 */
class VideoEmbedFieldFormatter extends Video {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      // Implement default settings.
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [
      // Implement settings form.
    ] + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    // Implement settings summary.
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    foreach ($items as $delta => $item) {
      $provider = $this->providerManager->loadProviderFromInput($item->value);
      if (!$provider) {
        $element[$delta] = ['#theme' => 'video_embed_field_missing_provider'];
      }
      else {
        $autoplay = ($this->currentUser->hasPermission('never autoplay videos')) ? FALSE : $this->getSetting('autoplay');
        $element[$delta] = $provider->renderEmbedCode($this->getSetting('width'), $this->getSetting('height'), $autoplay);
        $element[$delta]['#cache']['contexts'][] = 'user.permissions';
        $element[$delta] = [
          '#type' => 'container',
          '#attributes' => [
            'class' => [
              Html::cleanCssIdentifier(sprintf('video-embed-field-provider-%s', $provider->getPluginId())),
            ],
          ],
          'children' => $element[$delta],
        ];

        // For responsive videos, wrap each field item in it's own container.
        if ($this->getSetting('responsive')) {
          $element[$delta]['#attached']['library'][] = 'video_embed_field/responsive-video';
          $element[$delta]['#attributes']['class'][] = 'video-embed-field-responsive-video';
        }
        // Check if provider has constent require.
        $provider_service = \Drupal::service('icdc_didomi_embed.provider');
        $is_admin_route = \Drupal::service('router.admin_context')->isAdminRoute();
        if ($provider_service->hasRequireConsent($provider->getPluginId()) && !$is_admin_route) {
          $provider_data = $provider_service->getProviderData($provider->getPluginId());
          $element[$delta]['#theme'] = ['#theme' => 'icdc_didomi_video_embed'];
          $element[$delta]['#provider_data'] = $provider_data;
          $element[$delta]['#attached']['library'][] = 'icdc_didomi_video_embed/icdc_didomi_video_embed';
          $element['#attached']['drupalSettings']['icdc_didomi_embed']['providers'][$provider->getPluginId()] = [
            'vendor_id' => $provider_data['vendor']['id'],
          ];
        }

      }

    }

    return $element;
  }

}

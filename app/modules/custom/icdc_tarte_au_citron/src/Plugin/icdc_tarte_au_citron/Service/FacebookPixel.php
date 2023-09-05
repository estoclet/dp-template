<?php

namespace Drupal\icdc_tarte_au_citron\Plugin\icdc_tarte_au_citron\Service;

use Drupal\Core\Form\FormStateInterface;
use Drupal\icdc_tarte_au_citron\Annotation\IcdcTarteAuCitronService;
use Drupal\icdc_tarte_au_citron\ServicePluginBase;

/**
 * A Facebook Pixel service plugin.
 *
 * @IcdcTarteAuCitronService(
 *   id = "facebookpixel",
 *   title = @Translation("Facebook Pixel")
 * )
 */
class FacebookPixel extends ServicePluginBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'facebookpixelId' => '',
      ] + parent::defaultSettings();
  }

  /**
   * Implémentation du formulaire spécifique a
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $elements['facebookpixel']= array(
      '#type' => 'fieldset',
      '#title' => $this->t('Facebook Pixel'),
      '#states' => [
        'visible' => [
          ':input[id="edit-services-facebookpixel"]' => ['checked' => TRUE],
        ],
      ],
    );

    $elements['facebookpixel']['facebookpixelId'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Facebook pixel Id'),
      '#description' => $this->t('Parameter for tarte au citron @serviceName service.', ['@serviceName' => $this->getPluginDefinition()['title']]),
      '#default_value' => $this->getSetting('facebookpixelId'),
      '#states' => [
        'required' => [
          ':input[id="edit-services-facebookpixel"]' => ['checked' => TRUE],
        ],
      ],
      '#placeholder' => $this->t('YOUR-ID'),
      '#element_validate' => [[$this, 'validateFacebookPixelId']]
    ];

    $elements['facebookpixel']['facebookpixelMore'] = [
      '#type' => 'markup',
      '#markup' => $this->t('<p><strong>@varName</strong></p><p>You must implement the hook hook_icdc_tarte_au_citron_@pluginId_alter in a module and add a js file to define the function tarteaucitron.user.@functionName</p>', ['@varName' => 'Facebook pixel More', '@pluginId' => $this->getPluginId(), '@functionName' => 'facebookpixelMore'])
    ];

    return $elements;
  }

  /**
   * Form element validation handler for #type 'color'.
   */
  public function validateFacebookPixelId(&$element, FormStateInterface $form_state, &$complete_form) {
    $value = trim($element['#value']);
    if (empty($value) && !empty($form_state->getValue('services')[$this->getPluginId()]) ) { //case cochée
      $form_state->setError($element, $this->t('%name is required.', ['%name' => $element['#title']]));
    }
  }

}

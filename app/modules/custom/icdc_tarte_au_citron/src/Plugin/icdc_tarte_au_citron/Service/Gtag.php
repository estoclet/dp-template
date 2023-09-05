<?php

namespace Drupal\icdc_tarte_au_citron\Plugin\icdc_tarte_au_citron\Service;

use Drupal\Core\Form\FormStateInterface;
use Drupal\icdc_tarte_au_citron\Annotation\IcdcTarteAuCitronService;
use Drupal\icdc_tarte_au_citron\ServicePluginBase;

/**
 * A Google Analytics (gtag) service plugin.
 *
 * @IcdcTarteAuCitronService(
 *   id = "gtag",
 *   title = @Translation("Google Analytics (gtag.js)")
 * )
 */
class Gtag extends ServicePluginBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'gtagUa' => '',
        'gtagMore' => '',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $elements['gtag']= array(
      '#type' => 'fieldset',
      '#title' => $this->t('Google Analytics (gtag.js)'),
      '#states' => [
        'visible' => [
          ':input[id="edit-services-gtag"]' => ['checked' => TRUE],
        ],
      ],
    );

    $elements['gtag']['gtagUa'] = [
      '#type' => 'textfield',
      '#title' => $this->t('GtagUa'),
      '#description' => $this->t('Parameter for tarte au citron @serviceName service.', ['@serviceName' => $this->getPluginDefinition()['title']]),
      '#default_value' => $this->getSetting('gtagUa'),
      '#states' => [
        'required' => [
          ':input[id="edit-services-gtag"]' => ['checked' => TRUE],
        ],
      ],
      '#placeholder' => $this->t('example: @filePath', ['@filePath' => '/library/js/gtag.js']),
      '#element_validate' => [[$this, 'validateGtagUa']]
    ];

    $elements['gtag']['gtagMore'] = [
      '#type' => 'markup',
      '#markup' => $this->t('<p><strong>@varName</strong></p><p>You must implement the hook hook_icdc_tarte_au_citron_@pluginId_alter in a module and add a js file to define the function tarteaucitron.user.@functionName</p>', ['@varName' => 'GtagMore', '@pluginId' => $this->getPluginId(), '@functionName' => 'gtagMore'])
    ];

    return $elements;
  }

  /**
   * Form element validation handler for #type 'color'.
   */
  public function validateGtagUa(&$element, FormStateInterface $form_state, &$complete_form) {
    $value = trim($element['#value']);
    if (empty($value) && !empty($form_state->getValue('services')[$this->getPluginId()]) ) { //case cochée
      $form_state->setError($element, $this->t('%name is required.', ['%name' => $element['#title']]));
    }
  }

}

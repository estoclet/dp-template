<?php

namespace Drupal\icdc_tarte_au_citron\Plugin\icdc_tarte_au_citron\Service;

use Drupal\Core\Form\FormStateInterface;
use Drupal\icdc_tarte_au_citron\Annotation\IcdcTarteAuCitronService;
use Drupal\icdc_tarte_au_citron\ServicePluginBase;

/**
 * Google Analytics (ga.js) service plugin.
 *
 * @IcdcTarteAuCitronService(
 *   id = "gajs",
 *   title = @Translation("Google Analytics (ga.js)")
 * )
 */
class Gajs extends ServicePluginBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'gajsUa' => '',
        'gajsMore' => '',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $elements['gajs']= array(
      '#type' => 'fieldset',
      '#title' => $this->t('Google Analytics (ga.js)'),
      '#states' => [
        'visible' => [
          ':input[id="edit-services-gajs"]' => ['checked' => TRUE],
        ],
      ],
    );

    $elements['gajs']['gajsUa'] = [
      '#type' => 'textfield',
      '#title' => $this->t('GajsUa'),
      '#description' => $this->t('Parameter for tarte au citron @serviceName service.', ['@serviceName' => $this->getPluginDefinition()['title']]),
      '#default_value' => $this->getSetting('gtagUa'),
      '#states' => [
        'required' => [
          ':input[id="edit-services-gajs"]' => ['checked' => TRUE],
        ],
      ],
      '#placeholder' =>'UA-XXXXXXXX-X',
      '#element_validate' => [[$this, 'validateGajsUa']]
    ];

    $elements['gajs']['gajsMore'] = [
      '#type' => 'markup',
      '#markup' => $this->t('<p><strong>@varName</strong></p><p>You must implement the hook hook_icdc_tarte_au_citron_@pluginId_alter in a module and add a js file to define the function tarteaucitron.user.@functionName</p>', ['@varName' => 'GatjsMore', '@pluginId' => $this->getPluginId(), '@functionName' => 'gajsMore'])
    ];

    return $elements;
  }

  /**
   * Form element validation handler for #type 'color'.
   */
  public function validateGajsUa(&$element, FormStateInterface $form_state, &$complete_form) {
    $value = trim($element['#value']);
    if (empty($value) && !empty($form_state->getValue('services')[$this->getPluginId()]) ) { //case cochée
      $form_state->setError($element, $this->t('%name is required.', ['%name' => $element['#title']]));
    }
  }

}

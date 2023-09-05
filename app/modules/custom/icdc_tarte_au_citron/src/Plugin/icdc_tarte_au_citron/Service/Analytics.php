<?php

namespace Drupal\icdc_tarte_au_citron\Plugin\icdc_tarte_au_citron\Service;

use Drupal\Core\Form\FormStateInterface;
use Drupal\icdc_tarte_au_citron\Annotation\IcdcTarteAuCitronService;
use Drupal\icdc_tarte_au_citron\ServicePluginBase;

/**
 * A Google Analytics (universal) service plugin.
 *
 * @IcdcTarteAuCitronService(
 *   id = "analytics",
 *   title = @Translation("Google Analytics (universal)")
 * )
 */
class Analytics extends ServicePluginBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'analyticsUa' => '',
        'analyticsmore' => '',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $elements['analytics']= array(
      '#type' => 'fieldset',
      '#title' => $this->t('Google Analytics (universal)'),
      '#states' => [
        'visible' => [
          ':input[id="edit-services-analytics"]' => ['checked' => TRUE],
        ],
      ],
    );

    $elements['analytics']['analyticsUa'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Analytics Ua'),
      '#description' => $this->t('Parameter for tarte au citron @serviceName service.', ['@serviceName' => $this->getPluginDefinition()['title']]),
      '#default_value' => $this->getSetting('analyticsUa'),
      '#states' => [
        'required' => [
          ':input[id="edit-services-analytics"]' => ['checked' => TRUE],
        ],
      ],
      '#placeholder' => 'UA-XXXXXXXX-X',
      '#element_validate' => [[$this, 'validateAtLibUrl']]
    ];

    $elements['analytics']['analyticsMore'] = [
      '#type' => 'markup',
      '#markup' => $this->t('<p><strong>@varName</strong></p><p>You must implement the hook hook_icdc_tarte_au_citron_@pluginId_alter in a module and add a js file to define the function tarteaucitron.user.@functionName</p>', ['@varName' => 'AnalyticsMore', '@pluginId' => $this->getPluginId(), '@functionName' => 'analyticsMore'])
    ];

    return $elements;
  }

  /**
   * Form element validation handler for #type 'color'.
   */
  public function validateAtLibUrl(&$element, FormStateInterface $form_state, &$complete_form) {
    $value = trim($element['#value']);
    if (empty($value) && !empty($form_state->getValue('services')[$this->getPluginId()]) ) { //case cochée
      $form_state->setError($element, $this->t('%name is required.', ['%name' => $element['#title']]));
    }
  }

}

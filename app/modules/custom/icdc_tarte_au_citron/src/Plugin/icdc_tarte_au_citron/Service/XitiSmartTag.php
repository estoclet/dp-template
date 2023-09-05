<?php

namespace Drupal\icdc_tarte_au_citron\Plugin\icdc_tarte_au_citron\Service;

use Drupal\Core\Form\FormStateInterface;
use Drupal\icdc_tarte_au_citron\Annotation\IcdcTarteAuCitronService;
use Drupal\icdc_tarte_au_citron\ServicePluginBase;

/**
 * A Xiti Smart Tag service plugin.
 *
 * @IcdcTarteAuCitronService(
 *   id = "xiti_smarttag",
 *   title = @Translation("Xiti Smart Tag")
 * )
 */
class XitiSmartTag extends ServicePluginBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'atLibUrl' => '',
        'atMore' => '',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $elements['xiti_smarttag']= array(
      '#type' => 'fieldset',
      '#title' => $this->t('Xiti Smart Tag'),
      '#states' => [
        'visible' => [
          ':input[id="edit-services-xiti-smarttag"]' => ['checked' => TRUE],
        ],
      ],
    );

    $elements['xiti_smarttag']['atLibUrl'] = [
      '#type' => 'textfield',
      '#title' => $this->t('AtLibUrl'),
      '#description' => $this->t('Parameter for tarte au citron @serviceName service.', ['@serviceName' => $this->getPluginDefinition()['title']]),
      '#default_value' => $this->getSetting('atLibUrl'),
      '#states' => [
        'required' => [
          ':input[id="edit-services-xiti-smarttag"]' => ['checked' => TRUE],
        ],
      ],
      '#placeholder' => $this->t('example: @filePath', ['@filePath' => '/js/smarttag.js']),
      '#element_validate' => [[$this, 'validateAtLibUrl']]
    ];

    $elements['xiti_smarttag']['atMore'] = [
      '#type' => 'markup',
      '#markup' => $this->t('<p><strong>@varName</strong></p><p>You must implement the hook hook_icdc_tarte_au_citron_@pluginId_alter in a module and add a js file to define the function tarteaucitron.user.@functionName</p>', ['@varName' => 'AtMore', '@pluginId' => $this->getPluginId(), '@functionName' => 'atMore'])
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

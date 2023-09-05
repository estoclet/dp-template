<?php

namespace Drupal\icdc_tarte_au_citron\Plugin\icdc_tarte_au_citron\Service;

use Drupal\Core\Form\FormStateInterface;
use Drupal\icdc_tarte_au_citron\Annotation\IcdcTarteAuCitronService;
use Drupal\icdc_tarte_au_citron\ServicePluginBase;

/**
 * A Xiti service plugin.
 *
 * @IcdcTarteAuCitronService(
 *   id = "xiti",
 *   title = @Translation("AT Internet (Xiti)")
 * )
 */
class Xiti extends ServicePluginBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'xitiId' => '',
        'xitiMore' => '',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $elements['xiti']= array(
      '#type' => 'fieldset',
      '#title' => $this->t('AT Internet (Xiti)'),
      '#states' => [
        'visible' => [
          ':input[id="edit-services-xiti"]' => ['checked' => TRUE],
        ],
      ],
    );

    $elements['xiti']['xitiId'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Xiti Id'),
      '#description' => $this->t('Parameter for tarte au citron @serviceName service.', ['@serviceName' => $this->getPluginDefinition()['title']]),
      '#default_value' => $this->getSetting('xitiId'),
      '#states' => [
        'required' => [
          ':input[id="edit-services-xiti"]' => ['checked' => TRUE],
        ],
      ],
      '#placeholder' => $this->t('YOUR-ID'),
      '#element_validate' => [[$this, 'validateXitiId']]
    ];

    $elements['xiti']['xitiMore'] = [
      '#type' => 'markup',
      '#markup' => $this->t('<p><strong>@varName</strong></p><p>You must implement the hook hook_icdc_tarte_au_citron_@pluginId_alter in a module and add a js file to define the function tarteaucitron.user.@functionName</p>', ['@varName' => 'Xiti More', '@pluginId' => $this->getPluginId(), '@functionName' => 'xitiMore'])
    ];

    return $elements;
  }

  /**
   * Form element validation handler for #type 'color'.
   */
  public function validateXitiId(&$element, FormStateInterface $form_state, &$complete_form) {
    $value = trim($element['#value']);
    if (empty($value) && !empty($form_state->getValue('services')[$this->getPluginId()]) ) { //case cochée
      $form_state->setError($element, $this->t('%name is required.', ['%name' => $element['#title']]));
    }
  }

}

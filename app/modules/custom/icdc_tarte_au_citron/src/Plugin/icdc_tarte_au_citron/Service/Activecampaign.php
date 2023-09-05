<?php

namespace Drupal\icdc_tarte_au_citron\Plugin\icdc_tarte_au_citron\Service;

use Drupal\Core\Form\FormStateInterface;
use Drupal\icdc_tarte_au_citron\Annotation\IcdcTarteAuCitronService;
use Drupal\icdc_tarte_au_citron\ServicePluginBase;

/**
 * A Active Campaign service plugin.
 *
 * @IcdcTarteAuCitronService(
 *   id = "activecampaign",
 *   title = @Translation("Active Campaign")
 * )
 */
class Activecampaign extends ServicePluginBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'actid' => '',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $elements['activecampaign'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Active Campaign'),
      '#states' => [
        'visible' => [
          ':input[id="edit-services-activecampaign"]' => ['checked' => TRUE],
        ],
      ],
    );

    $elements['activecampaign']['actid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Actid'),
      '#description' => $this->t('Your unique tracking account ID (can be found on the Integrations page).'),
      '#default_value' => $this->getSetting('actid'),
      '#states' => [
        'required' => [
          ':input[id="edit-services-activecampaign"]' => ['checked' => TRUE],
        ],
      ],
      '#placeholder' => $this->t('YOUR-ID'),
      '#element_validate' => [[$this, 'validateActid']]
    ];

    return $elements;
  }
  /**
   * Form element validation handler for element #required.
   */
  public function validateActid(&$element, FormStateInterface $form_state, &$complete_form) {
    $value = trim($element['#value']);
    if (empty($value) && !empty($form_state->getValue('services')[$this->getPluginId()]) ) { //case cochée
      $form_state->setError($element, $this->t('%name is required.', ['%name' => $element['#title']]));
    }
  }
}

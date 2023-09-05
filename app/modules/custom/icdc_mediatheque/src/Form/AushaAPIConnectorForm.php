<?php

namespace Drupal\icdc_mediatheque\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
/**
 * Class AushaAPIConnectorForm.
 */
class AushaAPIConnectorForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'icdc_mediatheque.ausha_api_connector',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ausha_api_connector_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $configs = $this->config('icdc_mediatheque.ausha_api_connector')->getRawData();

    $form['url_ausha'] = [
        '#type' => 'textfield',
        '#title' => 'Url de l\'API ausha',
        '#description' => 'exemple : https://developers.ausha.co/v1/',
        '#default_value' => $configs['url'],
        '#required' => TRUE
    ];

    $form['token'] = [
        '#type' => 'textarea',
        '#title' => 'Jeton de connexion',
        '#description' => 'Fournit dans le compte utilisateur Ausha. Permet l\'authentification de Drupal auprès de l\'API Ausha.',
        '#default_value' => $configs['token'],
        '#required' => TRUE
    ];

    return parent::buildForm($form, $form_state);
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $configs = $this->config('icdc_mediatheque.ausha_api_connector')->getRawData();

    $form_values = $form_state->getValues();

    $this->config('icdc_mediatheque.ausha_api_connector')
      ->set('url', $form_values['url_ausha'])
      ->set('token', $form_values['token'])
      ->save();
  }
}

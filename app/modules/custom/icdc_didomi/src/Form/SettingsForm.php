<?php

namespace Drupal\icdc_didomi\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ICDC DIDOMI Settings Form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'icdc_didomi.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('icdc_didomi.settings');
    $form['public_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Public API Key'),
      '#default_value' => $config->get('public_api_key'),
      '#required' => TRUE,
    ];
    $form["paths_list_condition"] = [
      '#type' => 'radios',
      '#title' => $this->t('Insert snippet for specific paths'),
      '#options' => [
        'DIDOMI_CONSENT_EXCLUDE_LISTED' => $this->t('All paths except the listed paths'),
        'DIDOMI_CONSENT_INCLUDE_LISTED' => $this->t('Only the listed paths'),
      ],
      '#default_value' => !empty($config->get("paths_list_condition")) ? $config->get("paths_list_condition") : 'DIDOMI_CONSENT_EXCLUDE_LISTED',
    ];
    $form['paths_list'] = [
      '#type' => 'textarea',
      '#title' => $this->t("Listed paths"),
      '#description' => $this->t('Enter one relative path per line using the "*" character as a wildcard. Example paths are: "/node" for the node page, "/user/*" for each individual user, and "<front>" for the front page.'),
      '#default_value' => $config->get("paths_list"),
      '#rows' => 10,
    ];
    $form['publish'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Publish the consent notice.'),
      '#default_value' => $config->get('publish'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('icdc_didomi.settings')
      ->set('public_api_key', $form_state->getValue('public_api_key'))
      ->set('paths_list_condition', $form_state->getValue('paths_list_condition'))
      ->set('paths_list', $form_state->getValue('paths_list'))
      ->set('publish', $form_state->getValue('publish'))
      ->save();
  }

}

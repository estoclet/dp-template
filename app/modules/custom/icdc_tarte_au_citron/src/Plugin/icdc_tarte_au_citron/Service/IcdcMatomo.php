<?php

namespace Drupal\icdc_tarte_au_citron\Plugin\icdc_tarte_au_citron\Service;

use Drupal\Core\Form\FormStateInterface;
use Drupal\icdc_tarte_au_citron\Annotation\IcdcTarteAuCitronService;
use Drupal\icdc_tarte_au_citron\ServicePluginBase;

/**
 * A Matomo service plugin.
 *
 * @IcdcTarteAuCitronService(
 *   id = "icdc_matomo",
 *   title = @Translation("ICDC Matomo")
 * )
 */
class IcdcMatomo extends ServicePluginBase {

  protected function getLibraryName() {
    return 'icdc_tarte_au_citron/services_icdcmatomo';
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'url_tms' => '',
        'domain_tms' => '',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $elements['icdc_matomo']= array(
      '#type' => 'fieldset',
      '#title' => $this->t('ICDC Matomo'),
      '#states' => [
        'visible' => [
          ':input[id="edit-services-icdc-matomo"]' => ['checked' => TRUE],
        ],
      ],
    );
    $elements['icdc_matomo']['info']= array(
      '#markup' => $this->t('use icdc_matomo module\'s configuration')
    );

    $elements['icdc_matomo']['url_tms'] = [
      '#type' => 'value',
      '#value' => \Drupal::config('icdc_matomo.settings')->get('url_tms')
    ];

    $elements['icdc_matomo']['domain_tms'] = [
      '#type' => 'value',
      '#value' => \Drupal::config('icdc_matomo.settings')->get('domain_tms')
    ];

    return $elements;
  }

}

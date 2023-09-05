<?php

namespace Drupal\icdc_tarte_au_citron\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\icdc_tarte_au_citron\ServicesManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements an example form.
 */
class ConfigurationForm extends ConfigFormBase {

  /**
   * @var ServicesManagerInterface
   */
  protected $servicesManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, ServicesManagerInterface $servicesManager) {
    parent::__construct($config_factory);
    $this->servicesManager = $servicesManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('icdc_tarte_au_citron.services_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'configuration_form';
  }

  protected function getEditableConfigNames()
  {
    return ['icdc_tarte_au_citron.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Chosen settings:
    $config = $this->configFactory->get('icdc_tarte_au_citron.settings');

    $form['title_conf'] = array(
      '#type' => 'markup',
      '#markup' => '<h2><b>' . $this->t('Parameters') . '</b></h2>',
    );

    $form['hashtag'] = array(
      '#title' => 'Hashtag',
      '#description' => t('Open the panel with this hashtag.'),
      '#type' => 'textfield',
      '#default_value' => $config->get('hashtag')
    );

    $form['cookieName'] = array(
      '#title' => $this->t('Cookie Name'),
      '#description' => $this->t('Cookie name.'),
      '#type' => 'textfield',
      '#default_value' => $config->get('cookieName')
    );

    $form['orientation'] = array(
      '#title' => $this->t('Orientation'),
      '#description' => $this->t('Banner position (top - middle - bottom).'),
      '#type' => 'select',
      '#options' => [
        'top' => $this->t('Top'),
        'middle' => $this->t('Middle'),
        'bottom' => $this->t('Bottom'),
      ],
      '#required' => TRUE,
      '#default_value' => $config->get('orientation')
    );

    $form['showAlertSmall'] = array(
      '#title' => $this->t('Show alert small'),
      '#description' => $this->t('Show the small banner on bottom right.'),
      '#type' => 'checkbox',
      '#default_value' => $config->get('showAlertSmall')
    );

    $form['cookieslist'] = array(
      '#title' => $this->t('Cookies list'),
      '#description' => $this->t('Show the cookie list.'),
      '#type' => 'checkbox',
      '#default_value' => $config->get('cookieslist')
    );

    $form['adblocker'] = array(
      '#title' => 'Adblocker',
      '#description' => $this->t('Show a Warning if an adblocker is detected.'),
      '#type' => 'checkbox',
      '#default_value' => $config->get('adblocker')

    );

    $form['AcceptAllCta'] = array(
      '#title' => $this->t('Accept All Cta'),
      '#description' => $this->t('Show the accept all button when high Privacy on.'),
      '#type' => 'checkbox',
      '#default_value' => $config->get('adblocker')
    );

    $form['highPrivacy'] = array(
      '#title' => $this->t('High Privacy'),
      '#description' => $this->t('Disable auto consent.'),
      '#type' => 'checkbox',
      '#default_value' => $config->get('highPrivacy')
    );

    $form['handleBrowserDNTRequest'] = array(
      '#title' => $this->t('Handle browser do not track request'),
      '#description' => $this->t('If Do Not Track == 1, disallow all.'),
      '#type' => 'checkbox',
      '#default_value' => $config->get('handleBrowserDNTRequest')
    );

    $form['removeCredit'] = array(
      '#title' => $this->t('Remove credit'),
      '#description' => $this->t('Remove credit link.'),
      '#type' => 'checkbox',
      '#default_value' => $config->get('removeCredit')
    );

    $form['moreInfoLink'] = array(
      '#title' => $this->t('More info link'),
      '#description' => $this->t('Show more info link.'),
      '#type' => 'checkbox',
      '#default_value' => $config->get('moreInfoLink')
    );

    $form['useExternalCss'] = array(
      '#title' => $this->t('Use external css'),
      '#description' => $this->t('If not checked, the tarteaucitron.css file will be loaded. If you want to customize the appearance of tarte au citron component, add your own css file based on tarteaucitron.css in a module or theme.'),
      '#type' => 'checkbox',
      '#default_value' => $config->get('useExternalCss')
    );

    $form['privacyUrl'] = array(
      '#title' => $this->t('Privacy Url'),
      '#description' => $this->t('Privacy policy.'),
      '#type' => 'textfield',
      '#placeholder' =>  $this->t('relative path to privacy policy'),
      '#default_value' => $config->get('privacyUrl')
    );

    $form['readmoreLink'] = array(
      '#title' => $this->t('Read more link'),
      '#description' => $this->t('Change the default readmore link.'),
      '#type' => 'textfield',
      '#default_value' => $config->get('readmoreLink'),
    );

    $form['cookieDomain'] = array(
      '#title' => $this->t('Cookie Domain'),
      '#description' => $this->t('Shared cookie for multisite.'),
      '#type' => 'textfield',
      '#placeholder' => 'my-multisite-domaine.fr',
      '#default_value' => $config->get('cookieDomain')
    );

    $form['title_service'] = array(
      '#type' => 'markup',
      '#markup' => '<h2><b>' . $this->t('Services') . '</b></h2>',
    );

    $form['services'] = array(
      '#title' => $this->t('Services enabled'),
      '#description' => $this->t('Services enabled.'),
      '#type' => 'checkboxes',
      '#options' => $this->servicesManager->getServicesOptionList(),
      '#default_value' => $config->get('services'),
    );

    $form['services_settings'] = [
      '#type' => 'container',
      '#title' => $this->t('Services settings'),
      '#tree' => TRUE,
    ];
    foreach ($this->servicesManager->getServices() as $service) {
        $form['services_settings'] += $service->settingsForm($form, $form_state);
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('icdc_tarte_au_citron.settings');

    $config
      ->set('hashtag', $form_state->getValue('hashtag'))
      ->set('cookieName', $form_state->getValue('cookieName'))
      ->set('orientation', $form_state->getValue('orientation'))
      ->set('showAlertSmall', $form_state->getValue('showAlertSmall'))
      ->set('cookieslist', $form_state->getValue('cookieslist'))
      ->set('adblocker', $form_state->getValue('adblocker'))
      ->set('AcceptAllCta', $form_state->getValue('AcceptAllCta'))
      ->set('highPrivacy', $form_state->getValue('highPrivacy'))
      ->set('handleBrowserDNTRequest', $form_state->getValue('handleBrowserDNTRequest'))
      ->set('removeCredit', $form_state->getValue('removeCredit'))
      ->set('moreInfoLink', $form_state->getValue('moreInfoLink'))
      ->set('useExternalCss', $form_state->getValue('useExternalCss'))
      ->set('readmoreLink', $form_state->getValue('readmoreLink'))
      ->set('privacyUrl', $form_state->getValue('privacyUrl'))
      ->set('cookieDomain', $form_state->getValue('cookieDomain'))
      ->set('services', array_filter($form_state->getValue('services')))
      ->set('services_settings', $form_state->getValue('services_settings'));

    $config->save();

    parent::submitForm($form, $form_state);
  }

}

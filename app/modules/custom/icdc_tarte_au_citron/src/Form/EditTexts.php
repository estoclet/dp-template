<?php

namespace Drupal\icdc_tarte_au_citron\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class EditTexts.
 *
 * @package Drupal\tacjs\Form
 */
class EditTexts extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'edit_texts';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['icdc_tarte_au_citron.texts.settings'];
  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('icdc_tarte_au_citron.texts.settings');

    $texts = $this->getTexts();

    $localModuleInstalled = \Drupal::moduleHandler()->moduleExists('locale');
    if ($localModuleInstalled) {
      $translate = '<br />' . t('<b>Important, you need to use english in the @destination column</b> and then translate the text on the <a href=":translate">User interface translation</a> page.', [':translate' => Url::fromRoute('locale.translate_page')->toString(), '@destination' => $this->t('Destination')]);
    } else {
      $translate = '';
    }

    $form['texts'] = [
      '#prefix' => '<p>' . t('Enter the text that will be presented to your website users. The same text is used in both the cookie declaration and the consent dialog.') . $translate . '</p>',
      '#type' => 'table',
      '#header' => [
        'original' =>$this->t('Original'),
        'destination' =>$this->t('Destination'),
      ],
      '#rows' => [],
    ];

    foreach ($texts as $i => $currentText) {
      $configVal = $config->get($currentText['id']);
      $form['texts'][$i] = [
        'original' => [
          '#type' => 'inline_template',
          '#template' => '<strong>{{ msg }}</strong>',
          '#context' => [
            'msg' => $currentText['msg'],
          ],
        ],
        'destination' => [
          '#type' => 'textarea',
          '#title' => t('Destination'),
          '#title_display' => 'invisible',
          '#default_value' => !empty($configVal) && $configVal != $currentText['msg'] ? $configVal : NULL,
          '#description' => '',
        ]
      ];
      if($localModuleInstalled) {
        foreach (\Drupal::languageManager()->getLanguages() as $langcode => $language) {
          if($langcode == 'en') {
            continue;
          }
          $form['texts'][$i]['original']['#template'] .= '<div>{{ trans_label_' . $langcode .  ' }} : {{ trans_' . $langcode .  ' }}</div>';
          $form['texts'][$i]['original']['#context'] += [
            'trans_label_' . $langcode => t('Translation for @language', ['@language' => $language->getName()]),
            'trans_' . $langcode => t($currentText['msg'], [], ['langcode' => $langcode])
          ];
          if(!empty($configVal) && $configVal != $currentText['msg']) {
            $form['texts'][$i]['destination']['#description'] .= '<p>' . t('Translation for @language', ['@language' => $language->getName()]) . ' : ' . t($configVal, [], ['langcode' => $langcode]) . '</p>';
          }
        }
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('icdc_tarte_au_citron.texts.settings');

    $texts = $this->getTexts();

    $values = $form_state->getValue('texts');
    foreach ($texts as $i => $currentText) {
      $config->set($currentText['id'], !empty($values[$i]['destination']) ? $values[$i]['destination'] : $currentText['msg']);
    }

    $config->save();

    parent::submitForm($form, $form_state);
  }

  protected function getTexts() {
    return [
      ['id' => 'middleBarHead', 'msg' =>  '☝ 🍪'],
      ['id' => 'adblock', 'msg' =>  'Hello! This site is transparent and lets you chose the 3rd party services you want to allow.'],
      ['id' => 'adblock_call', 'msg' =>  'Please disable your adblocker to start customizing.'],
      ['id' => 'reload', 'msg' =>  'Refresh the page'],
      ['id' => 'alertBigScroll', 'msg' =>  'By continuing to scroll,'],
      ['id' => 'alertBigClick', 'msg' =>  'If you continue to browse this website,'],
      ['id' => 'alertBig', 'msg' =>  'you are allowing all third-party services'],
      ['id' => 'alertBigPrivacy', 'msg' =>  'This site uses cookies and gives you control over what you want to activate'],
      ['id' => 'alertSmall', 'msg' =>  'Manage services'],
      ['id' => 'personalize', 'msg' =>  'Personalize'],
      ['id' => 'acceptAll', 'msg' =>  'OK, accept all'],
      ['id' => 'close', 'msg' =>  'Close'],
      ['id' => 'privacyUrl', 'msg' =>  'Privacy policy'],
      ['id' => 'all', 'msg' =>  'Preference for all services'],
      ['id' => 'info', 'msg' =>  'Protecting your privacy'],
      ['id' => 'disclaimer', 'msg' =>  'By allowing these third party services, you accept their cookies and the use of tracking technologies necessary for their proper functioning.'],
      ['id' => 'allow', 'msg' =>  'Allow'],
      ['id' => 'deny', 'msg' =>  'Deny'],
      ['id' => 'noCookie', 'msg' =>  'This service does not use cookie.'],
      ['id' => 'useCookie', 'msg' =>  'This service can install'],
      ['id' => 'useCookieCurrent', 'msg' =>  'This service has installed'],
      ['id' => 'useNoCookie', 'msg' =>  'This service has not installed any cookie.'],
      ['id' => 'more', 'msg' =>  'Read more'],
      ['id' => 'source', 'msg' =>  'View the official website'],
      ['id' => 'credit', 'msg' =>  'Cookies manager by tarteaucitron.js'],
      ['id' => 'noServices', 'msg' =>  'This website does not use any cookie requiring your consent.'],
      ['id' => 'toggleInfoBox', 'msg' =>  'Show/hide informations about cookie storage'],
      ['id' => 'title', 'msg' =>  'Cookies management panel'],
      ['id' => 'cookieDetail', 'msg' =>  'Cookie detail for'],
      ['id' => 'ourSite', 'msg' =>  'on our site'],
      ['id' => 'newWindow', 'msg' =>  '(new window)'],
      ['id' => 'allowAll', 'msg' =>  'Allow all cookies'],
      ['id' => 'denyAll', 'msg' =>  'Deny all cookies'],
      ['id' => 'fallback', 'msg' =>  'is disabled.'],
      ['id' => 'ads_title', 'msg' =>  'Advertising network'],
      ['id' => 'ads_details', 'msg' =>  'Ad networks can generate revenue by selling advertising space on the site.'],
      ['id' => 'analytic_title', 'msg' =>  'Audience measurement'],
      ['id' => 'analytic_details', 'msg' =>  'The audience measurement services used to generate useful statistics attendance to improve the site.'],
      ['id' => 'social_title', 'msg' =>  'Social networks'],
      ['id' => 'social_details', 'msg' =>  'Social networks can improve the usability of the site and help to promote it via the shares.'],
      ['id' => 'video_title', 'msg' =>  'Videos'],
      ['id' => 'video_details', 'msg' =>  'Video sharing services help to add rich media on the site and increase its visibility.'],
      ['id' => 'comment_title', 'msg' =>  'Comments'],
      ['id' => 'comment_details', 'msg' =>  'Comments managers facilitate the filing of comments and fight against spam.'],
      ['id' => 'support_title', 'msg' =>  'Support'],
      ['id' => 'support_details', 'msg' =>  'Support services allow you to get in touch with the site team and help to improve it.'],
      ['id' => 'api_title', 'msg' =>  'APIs'],
      ['id' => 'api_details', 'msg' =>  'APIs are used to load scripts: geolocation, search engines, translations, ...'],
      ['id' => 'other_title', 'msg' =>  'Other'],
      ['id' => 'other_details', 'msg' =>  'Services to display web content.'],
    ];
  }

}

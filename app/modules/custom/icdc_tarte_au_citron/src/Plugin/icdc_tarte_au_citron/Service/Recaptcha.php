<?php

namespace Drupal\icdc_tarte_au_citron\Plugin\icdc_tarte_au_citron\Service;

use Drupal\icdc_tarte_au_citron\Annotation\IcdcTarteAuCitronService;
use Drupal\icdc_tarte_au_citron\ServicePluginBase;

/**
 * A reCAPTCHA service plugin.
 *
 * @IcdcTarteAuCitronService(
 *   id = "recaptcha_drupal",
 *   title = @Translation("reCAPTCHA")
 * )
 */
class Recaptcha extends ServicePluginBase {
  protected function getLibraryName() {
    return 'icdc_tarte_au_citron/services_recaptcha_drupal';
  }
}

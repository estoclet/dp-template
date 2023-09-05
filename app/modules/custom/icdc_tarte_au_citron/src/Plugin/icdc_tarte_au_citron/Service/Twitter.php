<?php

namespace Drupal\icdc_tarte_au_citron\Plugin\icdc_tarte_au_citron\Service;

use Drupal\icdc_tarte_au_citron\Annotation\IcdcTarteAuCitronService;
use Drupal\icdc_tarte_au_citron\ServicePluginBase;

/**
 * A Twitter service plugin.
 *
 * @IcdcTarteAuCitronService(
 *   id = "twitter_drupal",
 *   title = @Translation("Twitter for Drupal")
 * )
 */
class Twitter extends ServicePluginBase {

  protected function getLibraryName() {
    return 'icdc_tarte_au_citron/services_twitter_drupal';
  }
}

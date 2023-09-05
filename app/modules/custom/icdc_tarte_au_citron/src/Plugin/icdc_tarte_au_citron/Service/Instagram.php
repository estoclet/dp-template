<?php

namespace Drupal\icdc_tarte_au_citron\Plugin\icdc_tarte_au_citron\Service;

use Drupal\icdc_tarte_au_citron\Annotation\IcdcTarteAuCitronService;
use Drupal\icdc_tarte_au_citron\ServicePluginBase;

/**
 * A Instagram service plugin.
 *
 * @IcdcTarteAuCitronService(
 *   id = "instagram",
 *   title = @Translation("Instagram")
 * )
 */
class Instagram extends ServicePluginBase {

  protected function getLibraryName() {
    return 'icdc_tarte_au_citron/services_instagram';
  }

}

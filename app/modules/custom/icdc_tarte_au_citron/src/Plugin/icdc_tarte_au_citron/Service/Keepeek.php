<?php

namespace Drupal\icdc_tarte_au_citron\Plugin\icdc_tarte_au_citron\Service;

use Drupal\icdc_tarte_au_citron\Annotation\IcdcTarteAuCitronService;
use Drupal\icdc_tarte_au_citron\ServicePluginBase;

/**
 * A Keepeek service plugin.
 *
 * @IcdcTarteAuCitronService(
 *   id = "keepeek",
 *   title = @Translation("Keepeek")
 * )
 */
class Keepeek extends ServicePluginBase {

  protected function getLibraryName() {
    return 'icdc_tarte_au_citron/services_keepeek';
  }
}

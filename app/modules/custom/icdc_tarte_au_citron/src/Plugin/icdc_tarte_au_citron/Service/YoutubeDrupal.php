<?php

namespace Drupal\icdc_tarte_au_citron\Plugin\icdc_tarte_au_citron\Service;

use Drupal\icdc_tarte_au_citron\Annotation\IcdcTarteAuCitronService;
use Drupal\icdc_tarte_au_citron\ServicePluginBase;

/**
 * A YouTube service plugin.
 *
 * @IcdcTarteAuCitronService(
 *   id = "youtube_drupal",
 *   title = @Translation("YouTube Drupal")
 * )
 */
class YoutubeDrupal extends ServicePluginBase {

  protected function getLibraryName() {
    return 'icdc_tarte_au_citron/services_youtube_drupal';
  }

}

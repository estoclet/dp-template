<?php

namespace Drupal\icdc_tarte_au_citron\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a VideoEmbedProvider item annotation object.
 *
 * @Annotation
 */
class IcdcTarteAuCitronService extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The title of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $title;

}

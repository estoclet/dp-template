<?php

namespace Drupal\icdc_tarte_au_citron;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Providers an interface for embed providers.
 */
interface ServicePluginInterface extends PluginInspectionInterface {

  public function addJs(array &$page, array &$data);

  public function isEnabled();
}

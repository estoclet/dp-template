<?php

namespace Drupal\icdc_tarte_au_citron;

/**
 * Interface for the class that gathers the provider plugins.
 */
interface ServicesManagerInterface {

  /**
   * Get an options list suitable for services selection.
   *
   * @return array
   *   An array of options keyed by plugin ID with label values.
   */
  public function getServicesOptionList();


  /**
   * Get an array of Service Object which are enabled.
   *
   * @param bool $enabled
   *
   * @return \Drupal\icdc_tarte_au_citron\ServicePluginBase[]
   *   An array of enabled services.
   */
  public function getServices($enabled = FALSE);

  /**
   * Check if a service is enabled.
   *
   * @param string $serviceId
   *
   * @return bool
   *   TRUE if service is enabled, FALSE otherwise.
   */
  public function isServiceEnabled($serviceId);

}

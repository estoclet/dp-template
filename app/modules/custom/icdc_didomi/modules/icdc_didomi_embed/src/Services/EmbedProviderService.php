<?php

namespace Drupal\icdc_didomi_embed\Services;

use Drupal\Component\Serialization\Yaml;

/**
 * Class ProviderService.
 */
class EmbedProviderService {

  /**
   * Providers variable.
   *
   * @var array
   */
  private $providers = [];

  /**
   * Constructs a new ProviderService object.
   */
  public function __construct() {
    $this->setDataProviders();
  }

  /**
   * HasRequireConsent function.
   *
   * @param string $provider_plugin_id
   *   The provider id.
   *
   * @return bool
   *   True => is required, False is not required.
   */
  public function hasRequireConsent(string $provider_plugin_id):bool {

    if (!empty($this->providers[$provider_plugin_id]['constent']) && $this->providers[$provider_plugin_id]['constent'] === TRUE) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Get Provider Id By Url function.
   *
   * @param string $url
   *   The host.
   *
   * @return string|null
   *   The provider id.
   */
  public function getProviderIdByUrl(string $url):?string {
    $host = parse_url($url, PHP_URL_HOST);
    if (!empty($host)) {
      foreach ($this->providers as $key => $values) {
        if (!empty($values['domains'])) {
          foreach ($values['domains'] as $domain) {
            if ($this->endsWith($host, $domain)) {
              return $key;
            }
          }
        }
      }
    }
    return NULL;
  }

  /**
   * GetProviderData function.
   *
   * @param string $provider_plugin_id
   *   The provider id.
   *
   * @return array|null
   *   Array with the provider data.
   */
  public function getProviderData(string $provider_plugin_id):?array {
    if (!empty($this->providers[$provider_plugin_id])) {
      return $this->providers[$provider_plugin_id];
    }
    return NULL;
  }

  /**
   * SetDataProviders function.
   */
  private function setDataProviders() {
    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('icdc_didomi_embed')->getPath();
    $data = Yaml::decode(file_get_contents(DRUPAL_ROOT . '/' . $module_path . '/icdc_didomi_embed.providers.yml'));
    if (!empty($data['providers'])) {
      $this->providers = $data['providers'];
    }
    else {
      $this->providers = [];
    }
  }

  /**
   * Get All Providers function.
   *
   * @return array
   *   The providers data.
   */
  public function getProviders():array {
    return $this->providers;
  }

  /**
   * EndsWith function.
   *
   * @param string $haystack
   *   The string to search in.
   * @param string $needle
   *   The substring to search for in the haystack.
   *
   * @return bool
   *   Returns true if haystack ends with needle, false otherwise.
   */
  public static function endsWith($haystack, $needle):bool {
    $length = strlen($needle);
    if (!$length) {
      return TRUE;
    }
    return substr($haystack, -$length) === $needle;
  }

}

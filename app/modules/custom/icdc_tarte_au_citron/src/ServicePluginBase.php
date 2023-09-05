<?php

namespace Drupal\icdc_tarte_au_citron;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A base for the provider plugins.
 */
abstract class ServicePluginBase extends PluginBase implements ServicePluginInterface, ContainerFactoryPluginInterface {

  /**
   * The plugin is enabled.
   *
   * @var boolean
   */
  protected $enabled = FALSE;

  /**
   * The plugin settings.
   *
   * @var array
   */
  protected $settings = [];

  /**
   * Whether default settings have been merged into the current $settings.
   *
   * @var bool
   */
  protected $defaultSettingsMerged = FALSE;

  /**
   * Create a plugin with the given input.
   *
   * @param array $configuration
   *   The configuration of the plugin.
   * @param string $plugin_id
   *   The plugin id.
   * @param array $plugin_definition
   *   The plugin definition.
   *
   * @throws \Exception
   */
  public function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->enabled = $configuration['enabled'];
    if(!empty($configuration['settings'])) {
      $this->setSettings($configuration['settings']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition);
  }

  protected function getLibraryName() {
    return '';
  }

  public function addJs(array &$page, array &$data) {
    if(!empty($this->getLibraryName())) {
      if(empty($page['#attached'])) {
        $page['#attached'] = [
          'library' => []
        ];
      }
      else if(empty($page['#attached']['library'])) {
        $page['#attached']['library'] = [];
      }
      $page['#attached']['library'][] = $this->getLibraryName();
    }

    if(empty($data)) {
      $data = [];
    }
    $data += $this->getSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled() {
    return $this->enabled;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getSettings() {
    // Merge defaults before returning the array.
    if (!$this->defaultSettingsMerged) {
      $this->mergeDefaults();
    }
    return $this->settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getSetting($key) {
    // Merge defaults if we have no value for the key.
    if (!$this->defaultSettingsMerged && !array_key_exists($key, $this->settings)) {
      $this->mergeDefaults();
    }
    elseif ($this->enabled){
      return isset($this->settings[$key]) ? $this->settings[$key] : NULL;
    }
    else{
      return NULL;
    }
  }

  /**
   * Merges default settings values into $settings.
   */
  protected function mergeDefaults() {
    $this->settings += static::defaultSettings();
    $this->defaultSettingsMerged = TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function setSettings(array $settings) {
    $this->settings = $settings;
    $this->defaultSettingsMerged = FALSE;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setSetting($key, $value) {
    $this->settings[$key] = $value;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [];
  }

}

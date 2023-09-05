<?php

namespace Drupal\icdc_tarte_au_citron;


use Drupal\Component\Plugin\Mapper\MapperInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Gathers the services plugins.
 */
class ServicesManager extends DefaultPluginManager implements ServicesManagerInterface, MapperInterface {

  /**
   * List of all available services
   * @var array
   */
  protected $optionList = NULL;

  /**
   * @var \Drupal\Core\Config\ImmutableConfig|null
   */
  protected $config = NULL;

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/icdc_tarte_au_citron/Service', $namespaces, $module_handler, 'Drupal\icdc_tarte_au_citron\ServicePluginInterface', 'Drupal\icdc_tarte_au_citron\Annotation\IcdcTarteAuCitronService');
    $this->alterInfo('icdc_tarte_au_citron_services_info');
    $this->config = \Drupal::config('icdc_tarte_au_citron.settings');
  }

  /**
   * @inheritDoc
   */
  public function getServicesOptionList() {
    if(!isset($this->optionList)) {
      $this->optionList = [];
      foreach ($this->getDefinitions() as $definition) {
        $this->optionList[$definition['id']] = $definition['title'];
      }
    }
    return $this->optionList;
  }

  /**
   * @inheritDoc
   */
  public function getServices($enabled = FALSE) {
    $enabledServices = $this->config->get('services');

    $services = [];
    foreach($this->getServicesOptionList() as $currentServiceId => $currentServiceLabel) {
      if($enabled && empty($enabledServices[$currentServiceId])) {
        continue;
      }

      $config = !empty($this->config->get('services_settings')[$currentServiceId]) ? $this->config->get('services_settings')[$currentServiceId] : [];
      $services[$currentServiceId] = $this->createInstance($currentServiceId, ['enabled' => !empty($enabledServices[$currentServiceId]), 'settings' => $config]);
    }
    return $services;
  }

  /**
   * @inheritDoc
   */
  public function isServiceEnabled($serviceId) {
    $enabledServices = $this->config->get('services');
    return !empty($enabledServices[$serviceId]);
  }

}

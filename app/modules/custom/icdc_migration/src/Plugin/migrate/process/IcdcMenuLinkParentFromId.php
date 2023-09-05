<?php

namespace Drupal\icdc_migration\Plugin\migrate\process;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This plugin figures out menu link parent plugin IDs from id.
 *
 * @MigrateProcessPlugin(
 *   id = "icdc_menu_link_parent_from_id"
 * )
 */
class IcdcMenuLinkParentFromId extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected $menuLinkManager;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $menuLinkStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MenuLinkManagerInterface $menu_link_manager, EntityStorageInterface $menu_link_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->menuLinkManager = $menu_link_manager;
    $this->menuLinkStorage = $menu_link_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.menu.link'),
      $container->get('entity_type.manager')->getStorage('menu_link_content')
    );
  }

  /**
   * {@inheritdoc}
   *
   * Find the parent link GUID.
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (!$value) {
      // Top level item.
      return '';
    }
    try {
      if ($link = $this->menuLinkStorage->load($value)) {
        return $link->getPluginId();
      }
    }
    catch (MigrateSkipRowException $e) {

    }
  }

}

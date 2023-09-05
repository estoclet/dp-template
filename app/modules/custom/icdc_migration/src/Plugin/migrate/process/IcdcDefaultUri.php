<?php

namespace Drupal\icdc_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Use field or migration id.
 *
 * @MigrateProcessPlugin(
 *   id = "icdc_default_uri"
 * )
 */
class IcdcDefaultUri extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if($value) {
      return ['entity:' . $this->configuration['entity_type'] . '/' . $value];
    }
    return [$this->configuration['default_value']];
  }

}

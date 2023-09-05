<?php

namespace Drupal\access_config\Plugin\Block;

use Drupal\Core\Block\BlockBase;


/**
 * Provides a 'AccessConfig' Block dedicated to accessibility.
 *
 * @Block(
 *   id = "access_config",
 *   admin_label = @Translation("Bloc Access Config"),
 *   category = @Translation("Custom"),
 * )
 */
class AccessConfig extends BlockBase {

  /**
  * {@inheritdoc}
  */

  public function build() {
    return [
      '#theme' => 'access_config_block',
      '#attached' => [
        'library' => [
          'access_config/access_config',
          'access_config/custom',
        ]
      ]
    ];
  }
}

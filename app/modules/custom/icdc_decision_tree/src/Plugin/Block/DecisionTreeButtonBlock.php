<?php

namespace Drupal\icdc_decision_tree\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;

/**
 * Provides a 'DecisionTreeButtonBlock' block.
 *
 * @Block(
 *  id = "decision_tree_button_block",
 *  admin_label = @Translation("Decision tree button block"),
 * )
 */
class DecisionTreeButtonBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['#theme'] = 'decision_tree_button_block';

    return $build;
  }

}

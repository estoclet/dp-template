<?php

namespace Drupal\icdc_decision_tree\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;

/**
 * This block is used to display ICDC Decision tree.
 *
 * @Block(
 *  id = "decision_tree_block",
 *  admin_label = @Translation("Arbre de décision"),
 *  category = @Translation("ICDC")
 * )
 */
class DecisionTreeBlock extends DecisionTreeBlockAbstract {

  /**
   * {@inheritdoc}
   */
  protected function getHtmlId() {
    return 'icdcDecisionTree';
  }

  /**
   * {@inheritdoc}
   */
  protected function getVocabularyId() {
    return 'decision_tree';
  }

  /**
   * @return bool
   */
  protected function getNeedClose() {
    if (\Drupal::service('path.matcher')->isFrontPage()) {
      return FALSE;
    }

    return TRUE;
  }

}

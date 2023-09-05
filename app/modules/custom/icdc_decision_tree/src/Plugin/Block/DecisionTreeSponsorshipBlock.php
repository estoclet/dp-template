<?php

namespace Drupal\icdc_decision_tree\Plugin\Block;

use Drupal\Core\Cache\Cache;

/**
 * This block is used to display ICDC Decision tree.
 *
 * @Block(
 *  id = "decision_tree_sponsortship_block",
 *  admin_label = @Translation("Arbre de décision mécénat"),
 *  category = @Translation("ICDC")
 * )
 */
class DecisionTreeSponsorshipBlock extends DecisionTreeBlockAbstract {

  /**
   * @inheritDoc
   */
  protected function getHtmlId() {
    return 'icdcSponsorshipDecisionTree';
  }

  /**
   * @inheritDoc
   */
  protected function getVocabularyId() {
    return 'decision_tree_sponsorship';
  }

}

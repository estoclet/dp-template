<?php

namespace Drupal\icdc_decision_tree\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class SponsorshipDecisionTreeController.
 */
class SponsorshipDecisionTreeController extends ControllerBase {

  /**
   * Page.
   *
   * @return array
   *   Return sponsorship decision tree.
   */
  public function page() {
    $tree = \Drupal::service('icdc_decision_tree')->buildDecisionTree('decision_tree_sponsorship');

    $build = [];
    $build['#theme'] = 'decision_tree_page';
    $build['#decision_tree'] = $tree['decision_tree'];
    $build['#root'] = key($tree['decision_tree']);
    $build['#id'] = 'icdcSponsorshipDecisionTree';
    $build['#attached'] = [
      'library' => [
        'icdc_decision_tree/decision_tree',
      ],
      'drupalSettings' => [
        'sponsorshipDecisionTree' => $tree['decision_tree'],
        'sponsorshipDecisionTreeParents' => $tree['parents']
      ]
    ];

    return $build;
  }

  public function getTitle() {
    $root = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
      ->loadTree('decision_tree_sponsorship', 0, 1, FALSE)[0];

    return  $root->name;
  }
}

<?php

namespace Drupal\icdc_decision_tree\Service;

use Drupal\Component\Utility\Html;

/**
 * Class DecisionTreeService.
 */
class DecisionTreeService {

  /**
   * Constructs a new DecisionTreeService object.
   */
  public function __construct() {

  }

  public function buildDecisionTree($taxonomy_id) {
    $tree = [
      'title' => '',
      'decision_tree' => []
    ];
    $taxonomy_terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree($taxonomy_id, 0, NULL, TRUE);

    $terms_by_id = [];
    $title_parent = [];
    foreach ($taxonomy_terms as $term) {
      $terms_by_id[$term->tid->value] = $term;

      if(!empty($tree['title']) && $term->parents[0] == 0) {
        break;
      }

      if($term->parents[0] == 0) {
        $tree['title'] = $term->name->value;
      }
      else {
        $current_parent = $term->parents[0];
        $parents = [];
        while($current_parent != 0) {
          array_unshift($parents, $current_parent);
          $current_parent = $terms_by_id[$current_parent]->parents[0];
        }

        $parentId = $term->parents[0];
        $title = (isset($title_parent[$parentId]) ? $title_parent[$parentId] . ' ' : '') . $terms_by_id[$parentId]->field_tree_keyword->value . ' ' . $term->name->value;
        $title_parent[$term->tid->value] = $title;

        $new_item = [
          'parent' => $parentId,
          'parents' => $parents,
          'id' => $term->tid->value,
          'label_left' => $terms_by_id[$parentId]->field_tree_keyword->value,
          'label_right' => $term->name->value,
          'title' => Html::escape($title)
        ];

        if ($term->field_tree_level->value == 'final') {
          $new_item['link'] = [
            'uri' => $term->field_tree_link[0]->getUrl()->toString(),
            'title' => $term->field_tree_link->title,
          ];
        }

        $tree['decision_tree'][] = $new_item;
      }
    }

    return $tree;
  }

}

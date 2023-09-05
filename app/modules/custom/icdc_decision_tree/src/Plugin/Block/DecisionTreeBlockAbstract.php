<?php

namespace Drupal\icdc_decision_tree\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;

abstract class DecisionTreeBlockAbstract extends BlockBase {

  /**
   * @var bool
   */
  protected $need_close = TRUE;

  /**
   * {@inheritdoc}
   */
  public function build() {
    $tree = \Drupal::service('icdc_decision_tree')->buildDecisionTree($this->getVocabularyId());
    $build = [];
    $build['#theme'] = 'decision_tree_accordion';
    $build['#decision_tree'] = $tree['decision_tree'];
    $build['#id'] = $this->getHtmlId();
    $build['#title'] = $tree['title'];

    $build['#attached'] = [
      'library' => [
        'icdc_decision_tree/decision_tree',
      ],
      'drupalSettings' => [
        'decision_tree' => [
          $this->getHtmlId() => [
            'need_close' => $this->getNeedClose()
          ]
        ]
      ]
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), ['taxonomy_term_list']);
  }

  /**
   * @return string
   */
  abstract protected function getHtmlId();

  /**
   * @return string
   */
  abstract protected function getVocabularyId();

  /**
   * @return bool
   */
  protected function getNeedClose() {
    return TRUE;
  }
}

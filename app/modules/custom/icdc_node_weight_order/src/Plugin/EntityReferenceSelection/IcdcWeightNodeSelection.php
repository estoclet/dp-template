<?php

namespace Drupal\icdc_node_weight_order\Plugin\EntityReferenceSelection;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;

/**
 * Provides specific access control for the node entity type.
 *
 * @EntityReferenceSelection(
 *   id = "icdc:icdcweight",
 *   label = @Translation("Icdc Weight Node selection"),
 *   entity_types = {"node"},
 *   group = "icdc",
 *   weight = 1
 * )
 */
class IcdcWeightNodeSelection extends DefaultSelection {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'id_keywords' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS') {
    $query = parent::buildEntityQuery($match, $match_operator);
    $query->condition('status', NodeInterface::PUBLISHED);
    return $query;
  }

  public function entityQueryAlter(SelectInterface $query) {
    $kid = $this->getConfiguration()['id_keywords'];
    $query->leftJoin('icdc_node_weight_order_nodes', 'inwon', 'inwon.nid = base_table.nid AND inwon.kid = :kid', [':kid' => $kid]);
    $query->fields('inwon', ['nid']);
    $query->condition('inwon.nid', NULL, 'IS NULL');
  }
}

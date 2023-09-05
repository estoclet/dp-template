<?php

/**
 * @file
 * Contains \Drupal\icdc_node_weight_order\IcdcNodeWeightManager.
 */

namespace Drupal\icdc_node_weight_order;

use Drupal\Core\Database\Connection;
use Drupal\search_api\Plugin\search_api\datasource\ContentEntity;
use Drupal\node\Entity\Node;
use PDO;

/**
 * Provide utilities for creating content.
 */
class IcdcNodeWeightManager {

  /**
   * The db connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $db;

  /**
   * Constructs a EntityCreateAccessCheck object.
   *
   * @param \Drupal\Core\Database\Connection $db
   *   The db connection.
   */
  public function __construct(Connection $db) {
    $this->db = $db;
  }

  /**
   * Search a keyword element
   * @param string $keywords
   *
   * @return StdClass|boolean
   */
  public function searchKeyword($keywords) {
    $sth = $this->db->select('icdc_node_weight_order_keywords', 'k')
      ->fields('k')
      ->condition('k.keywords', $keywords, 'LIKE');

    $data = $sth->execute();
    $results = $data->fetchObject();
    if(!empty($results)) {
      return $results;
    }

    return FALSE;
  }

  /**
   * Get a keyword element by id
   * @param int $idKeywords
   *
   * @return StdClass|boolean
   */
  public function getKeyword($idKeywords) {
    $sth = $this->db->select('icdc_node_weight_order_keywords', 'k')
      ->fields('k');
    $sth->condition('k.id', (int) $idKeywords);

    $data = $sth->execute();
    $results = $data->fetchObject();
    if(!empty($results)) {
      return $results;
    }

    return FALSE;
  }

  /**
   * @return mixed
   */
  public function getAllKeywords() {
    $sth = $this->db->select('icdc_node_weight_order_keywords', 'k')
      ->fields('k')
      ->orderBy('k.keywords');
    $data = $sth->execute();
    return $data->fetchAll(PDO::FETCH_OBJ);
  }

  /**
   * Get a keyword element
   * @param string $keywords
   *
   * @return int
   */
  public function addKeyword($keywords) {
    $result = $this->db->insert('icdc_node_weight_order_keywords')
      ->fields([
        'keywords' => $keywords
      ])
      ->execute();

    return $result;
  }

  /**
   * Delete a keyword element by id
   * @param int $idKeywords
   *
   */
  public function deleteKeyword($idKeywords) {
    //get node id before delete
    $nids = [];
    $results = $this->getNodesOrderByKeyword($idKeywords, ['nid'], FALSE);
    if(!empty($results)) {
      foreach($results as $current) {
        $nids[] = $current->nid;
      }
    }

    $this->db->delete('icdc_node_weight_order_nodes')
      ->condition('kid', (int) $idKeywords)
      ->execute();
    $this->db->delete('icdc_node_weight_order_keywords')
      ->condition('id', (int) $idKeywords)
      ->execute();

    if(!empty($nids)) {
      $this->markNodesForReindex($nids);
    }
  }

  /**
   * Get node order by keyword id
   * @param int $idKeywords
   *
   * @return []
   */
  public function getNodesOrderByKeyword($idKeywords, $fields = [], $load = TRUE) {
    $sth = $this->db->select('icdc_node_weight_order_nodes', 'n')
      ->fields('n', $fields)
      ->orderBy('n.weight');
    $sth->condition('n.kid', (int) $idKeywords);
    $data = $sth->execute();
    $results = $data->fetchAll(PDO::FETCH_OBJ);
    if($load && !empty($results)) {
      foreach($results as $current) {
        $nids[$current->nid] = $current;
      }
      $nodes = Node::loadMultiple(array_keys($nids));
      foreach($nids as $nid => $obj) {
        $obj->entity = $nodes[$nid];
      }

      $results = array_values($nids);
    }
    return $results;
  }

  /**
   * Get node order by keyword id
   * @param int $nid
   *
   * @return []
   */
  public function getNodesOrderByNid($nid) {
    $sth = $this->db->select('icdc_node_weight_order_nodes', 'n');
    $sth->join('icdc_node_weight_order_keywords', 'k', 'n.kid = k.id');
    $sth->fields('n', ['weight'])
      ->fields('k', ['keywords'])
      ->condition('n.nid', (int) $nid);
    $data = $sth->execute();
    $results = $data->fetchAll(PDO::FETCH_ASSOC);
    if(!empty($results)) {
      array_walk($results, function(&$val) {
        $val['weight'] = (int) $val['weight'];
      });
    }
    else {
      $results = [
        [
          'keywords' => '',
          'weight' => 0
        ]
      ];
    }

    return $results;
  }

  /**
   * Add node order
   * @param int $idKeywords
   * @param int $nid
   * @param int $weight
   *
   * @return []
   */
  public function addNodesOrder($idKeywords, $nid, $weight = NULL) {
    if(is_null($weight)) {
      $sth = $this->db->select('icdc_node_weight_order_nodes', 'n');
      $sth->condition('n.kid', (int) $idKeywords);
      $sth->addExpression('MAX(weight)', 'max');
      $data = $sth->execute();
      if(($res = $data->fetchAssoc()) && !is_null($res['max'])) {
        $weight = (int) $res['max'] + 1;
      }
      else {
        $weight = 0;
      }
    }
    $ret = $this->db->insert('icdc_node_weight_order_nodes')
      ->fields([
        'kid' => (int) $idKeywords,
        'nid' => (int) $nid,
        'weight' => (int) $weight
      ])
      ->execute();

    $this->markNodesForReindex([$nid]);

    return $ret;
  }

  /**
   * Update node order
   * @param int $idKeywords
   * @param int $nid
   * @param int $weight
   *
   * @return []
   */
  public function updateNodesOrder($idKeywords, $nid, $weight) {
    $ret = $this->db->update('icdc_node_weight_order_nodes')
      ->fields([
        'weight' => $weight
      ])
      ->condition('kid', $idKeywords)
      ->condition('nid', $nid)
      ->execute();
    $this->markNodesForReindex([$nid]);

    return $ret;
  }

  /**
   * Delete node order
   * @param int $idKeywords
   * @param int $nid
   *
   * @return []
   */
  public function deleteNodesOrder($idKeywords, $nid) {
    $ret = $this->db->delete('icdc_node_weight_order_nodes')
      ->condition('kid', $idKeywords)
      ->condition('nid', $nid)
      ->execute();

    $this->markNodesForReindex([$nid]);

    return $ret;
  }

  protected function markNodesForReindex($nids) {
    $nodes = Node::loadMultiple($nids);
    foreach ($nodes as $node) {
      $indexes = ContentEntity::getIndexesForEntity($node);
      if (!$indexes) {
        break;
      }

      // Compute the item IDs for all languages of the entity.
      $item_ids = [];
      $entity_id = $node->id();
      foreach (array_keys($node->getTranslationLanguages()) as $langcode) {
        $item_ids[] = $entity_id . ':' . $langcode;
      }
      $datasource_id = 'entity:' . $node->getEntityTypeId();
      foreach ($indexes as $index) {
        $filtered_item_ids = ContentEntity::filterValidItemIds($index, $datasource_id, $item_ids);
        $index->trackItemsUpdated($datasource_id, $filtered_item_ids);
      }
    }
  }
}

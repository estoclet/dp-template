<?php

namespace Drupal\mini_sites;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\mini_sites\Entity\MiniSitePageInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Helper for mini site page content.
 */
class MiniSitePageContentHelper {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /*
   * @var EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  public function __construct(ConfigFactoryInterface $config_factory, EntityFieldManagerInterface $entityFieldManager) {
    $this->configFactory = $config_factory;
    $this->entityFieldManager =  $entityFieldManager;
  }

  /**
   * Remove page association
   *
   * @param \Drupal\node\NodeInterface $node
   */
  public function cleanSitePage(NodeInterface $node){
    $node->field_site_page->target_id = NULL;
    $node->field_site_page_type->value = NULL;
  }

  public function getPageRef(NodeInterface $node) {
    $contentPage = &drupal_static('mini_site_page_content_page_ref_' . $node->id());
    if(is_null($contentPage)) {
      $contentPage = FALSE;
      $config = $this->configFactory->get('mini_sites.settings');
      if(in_array($node->getType(), $config->get('node_type_field_site.node_types') ?:[])) {
        $fieldMap = $this->entityFieldManager->getFieldMapByFieldType('mini_site_entity_reference');
        $query = \Drupal::entityQuery('mini_site_page');
        $query->condition('status', 1);
        $query->sort('id', 'DESC');
        $query->range(0, 1);
        $condition = $query->orConditionGroup();
        foreach($fieldMap['mini_site_page'] as $field_name => $field) {
          $condition->condition($field_name, $node->id());
        }
        $query->condition($condition);
        $res = $query->execute();
        $contentPage = (int) array_shift($res);
      }
    }

    return $contentPage;
  }

  public function getPageView(NodeInterface $node) {
    $contentPage = &drupal_static('mini_site_page_content_page_view_' . $node->id());
    if(is_null($contentPage)) {
      $contentPage = FALSE;
      $config = $this->configFactory->get('mini_sites.settings');
      if(in_array($node->getType(), $config->get('node_type_field_site.node_types') ?:[])) {
        $fieldMap = $this->entityFieldManager->getFieldMapByFieldType('mini_site_view');
        $query = \Drupal::entityQuery('mini_site_page');
        $query->condition('status', 1);
        $query->sort('id', 'DESC');
        $query->range(0, 1);
        $condition = $query->orConditionGroup();
        foreach($fieldMap['mini_site_page'] as $field_name => $field) {
          $condition->condition($field_name . '.view_target_bundle', $node->getType());
        }
        $query->condition($condition);
        $res = $query->execute();
        $contentPage = (int) array_shift($res);
      }
    }

    return $contentPage;
  }

  /**
   * Set the page id of a node
   *
   * @param \Drupal\node\NodeInterface $node
   */
  public function setPage(NodeInterface $node) {
    $pageId = $pageType = NULL;
    if(!empty($contentPage = $this->getPageRef($node))) {
      $pageType = 'mini_site_entity_reference';
      $pageId = $contentPage;
    }
    else if(!empty($contentPage = $this->getPageView($node))) {
      $pageType = 'mini_site_view';
      $pageId = $contentPage;
    }

    $contentPage = [
      'id' => $pageId,
      'type' => $pageType,
    ];
    \Drupal::moduleHandler()->alter('mini_site_find_page', $contentPage, $node);

    if(!empty($contentPage['id'])) {
      $node->field_site_page->target_id = $contentPage['id'];
      $node->field_site_page_type->value = $contentPage['type'];
    }
    else {
      $node->field_site_page->target_id = NULL;
      $node->field_site_page_type->value = NULL;
    }
  }

  public function savePage(MiniSitePageInterface $miniSitePage) {
    $config = $this->configFactory->get('mini_sites.settings');
    $nodeTypes = $config->get('node_type_field_site.node_types') ?:[];
    if(empty($nodeTypes)) {
      return;
    }

    $entityFieldDefinitions = array_keys($miniSitePage->getFieldDefinitions());
    $fieldMap = $this->entityFieldManager->getFieldMapByFieldType('mini_site_entity_reference');
    $refFieldDefinitions = [];
    if(!empty($fieldMap['mini_site_page'])) {
      $refFieldDefinitions = array_intersect($entityFieldDefinitions, array_keys($fieldMap['mini_site_page']));
    }
    if(!empty($refFieldDefinitions)) {
      //if mini_site_page content reference field to a node, we need to update field_site_page of the referenced node
      foreach($refFieldDefinitions as $currentField) {
        //if old ref exists
        if(isset($miniSitePage->original) && !empty($miniSitePage->original->{$currentField}->target_id) && $miniSitePage->original->{$currentField}->target_id != $miniSitePage->{$currentField}->target_id) {
          $oldNode = Node::load($miniSitePage->original->{$currentField}->target_id);
          $this->cleanSitePage($oldNode);
          $this->setPage($oldNode);
          $oldNode->save();
        }

        if(!empty($miniSitePage->{$currentField}->target_id)) {
          $newNode = Node::load($miniSitePage->{$currentField}->target_id);
          $newNode->field_site_page->target_id = $miniSitePage->id();
          $newNode->field_site_page_type->value = 'mini_site_entity_reference';
          $newNode->save();
        }
      }
    }

    $fieldMap = $this->entityFieldManager->getFieldMapByFieldType('mini_site_view');
    $viewFieldDefinitions = [];
    if(!empty($fieldMap['mini_site_page'])) {
      $viewFieldDefinitions = array_intersect($entityFieldDefinitions, array_keys($fieldMap['mini_site_page']));
    }
    if(!empty($viewFieldDefinitions)) {
      //if mini_site_page content view field, we need to update field_site_page of all type node
      foreach ($viewFieldDefinitions as $currentField) {
        $value = $miniSitePage->{$currentField}->getValue()[0];
        $bundles = array_intersect($nodeTypes, [$value['view_target_bundle']]);
        $query = \Drupal::entityQuery('node');
        $query->condition('type', $bundles);
        $query->condition($query->orConditionGroup()
          ->notExists('field_site_page_type')
          ->condition('field_site_page_type', 'mini_site_entity_reference', 'NOT LIKE') //prevent to override à content which is linked to a reference page type
        );
        $query->condition('field_site', $miniSitePage->mini_site->target_id);
        $nids = $query->execute();
        foreach(Node::loadMultiple($nids) as $node) {
          $node->field_site_page->target_id = $miniSitePage->id();
          $node->field_site_page_type->value = 'mini_site_view';
          $node->save();
        }
      }
    }
  }

  public function deletePage(MiniSitePageInterface $miniSitePage) {
    $config = $this->configFactory->get('mini_sites.settings');
    if(empty($config->get('node_type_field_site.node_types'))) {
      return;
    }

    $query = \Drupal::entityQuery('node');
    $query->condition('field_site_page', $miniSitePage->id());
    $nids = $query->execute();
    $nodes = Node::loadMultiple($nids);
    foreach($nodes as $currentNode) {
      $this->cleanSitePage($currentNode);
      $this->setPage($currentNode);
      $currentNode->save();
    }
  }
}

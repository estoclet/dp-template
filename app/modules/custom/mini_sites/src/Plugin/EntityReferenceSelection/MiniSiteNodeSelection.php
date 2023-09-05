<?php

namespace Drupal\mini_sites\Plugin\EntityReferenceSelection;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;

/**
 * Provides specific access control for the node entity type with mini site.
 * @EntityReferenceSelection(
 *   id = "mini_site_content",
 *   label = @Translation("Default Mini Site"),
 *   group = "mini_site_content",
 *   weight = 0,
 *   deriver = "Drupal\Core\Entity\Plugin\Derivative\DefaultSelectionDeriver"
 * )
 */
class MiniSiteNodeSelection extends DefaultSelection {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'target_bundles' => NULL,
      'mini_site' => NULL,
    ] + parent::defaultConfiguration();
  }


  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    unset($form['target_bundles'], $form['target_bundles_update'], $form['auto_create']);
    if(isset($form['auto_create_bundle'])) {
      unset($form['auto_create_bundle']);
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS') {
    $query = parent::buildEntityQuery($match, $match_operator);
    $configuration = $this->getConfiguration();
    $target_type = $configuration['target_type'];
    $entity_type = $this->entityTypeManager->getDefinition($target_type);

    $miniSitesConfig = \Drupal::config('mini_sites.settings');
    $target_bundles = array_keys($miniSitesConfig->get('node_type_field_site.node_types'));
    if(!empty($target_bundles)) {
      $query->condition($entity_type->getKey('bundle'), $target_bundles, 'IN');
    }
    else {
      $query->condition($entity_type->getKey('bundle'), NULL, 'IS NULL');
    }

    if(!empty($configuration['mini_site'])) {
      $query->condition('field_site', $configuration['mini_site']);
    }

    return $query;
  }
}

<?php
/**
 * @file
 * Contains Drupal\Drupal\mini_sites\Form\MiniSiteAdminForm.
 */
namespace Drupal\mini_sites\Form;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\FieldConfigStorage;
use Drupal\mini_sites\Entity\MiniSitePage;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\views\Entity\View;
use Drupal\views\ViewEntityInterface;

class MiniSiteAdminForm extends ConfigFormBase {

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return [
      'mini_sites.settings',
    ];
  }

  /**
   * Returns a unique string identifying the form.
   *
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'mini_sites_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('mini_sites.settings');

    $options = array_map(function (NodeType $nodeType) { return $nodeType->label(); }, NodeType::loadMultiple());
    $form['node_type_field_site'] = [
      '#title' => t('The node types we can associate with a site entity.'),
      '#type' => 'fieldset',
    ];

    $form['node_type_field_site']['node_types'] = [
      '#title' => t(''),
      '#type' => 'checkboxes',
      '#options' => $options,
      '#default_value' => $form_state->getValue('node_types', $config->get('node_type_field_site.node_types')),
    ];

    $blocks_ids = $config->get('block_ids');
    if(empty($blocks_ids)) {
      $blocks_ids = [];
    }
    $form['block_ids'] = [
      '#title' => t('Blocks keep.'),
      '#type' => 'textarea',
      '#description' => t('Fill with blocks ids, one per line. Block listed will be keep during rendering page.'),
      '#default_value' => $form_state->getValue('block_ids', implode(PHP_EOL, $blocks_ids)),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Ajax callback for the color dropdown.
   */
  public static function updateDisplay(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#display-wrapper', $form['site_content_field']['display']));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $config = $this->config('mini_sites.settings');
    $config->set('node_type_field_site.node_types', array_filter($form_state->getValue('node_types')));
    $blocks = explode(PHP_EOL, str_replace("\r", '', $form_state->getValue('block_ids')));
    array_walk($blocks, 'trim');
    $config->set('block_ids', $blocks);
    $config->save();

    foreach ($form_state->getValue('node_types') as $bundle => $info) {
      $this->field_assign('node', $bundle, !empty($info));
    }
    // We need to build custom dynamic routes and menus.
    drupal_flush_all_caches();
  }

  /**
   * Creates our site field for an entity bundle.
   *
   * @param string $entity_type
   *   The entity type being created. Node and user are supported.
   * @param string $bundle
   *   The bundle being created.
   *
   * This function is here for convenience during installation. It is not really
   * an API function. Modules wishing to add fields to non-node entities must
   * provide their own field storage.
   *
   * @see micro_node_node_type_insert()
   * @see micro_node_install()
   */
  protected function field_assign($entity_type, $bundle, $create = TRUE) {
    $fields = [
      'field_site' => [
        'label' => 'Site',
        'target_type' => 'mini_site'
      ],
      'field_site_page' => [
        'label' => 'Site page',
        'target_type' => 'mini_site_page'
      ],
      'field_site_page_type' => [
        'label' => 'Site page type'
      ]
    ];
    if($create) {
      // Assign the field_site and field_site_page fields.
      foreach($fields as $currentField => $currentFieldConfig) {
        if(!FieldStorageConfig::loadByName($entity_type, $currentField)) {
          if(!empty($currentFieldConfig['target_type'])) {
            FieldStorageConfig::create(array(
              'field_name' => $currentField,
              'entity_type' => $entity_type,
              'type' => 'entity_reference',
              'cardinality' => 1,
              'settings' => [
                'target_type' => $currentFieldConfig['target_type']
              ]
            ))->save();
          }
          else {
            FieldStorageConfig::create(array(
              'field_name' => $currentField,
              'entity_type' => $entity_type,
              'type' => 'string',
              'cardinality' => 1
            ))->save();
          }
        }
        if(!FieldConfig::loadByName($entity_type, $bundle, $currentField)) {
          FieldConfig::create([
            'field_name' => $currentField,
            'entity_type' => $entity_type,
            'bundle' => $bundle,
            'label' => $currentFieldConfig['label'],
          ])->save();
        }
      }

      // Tell the form system how to behave. Default to auto complete.
      /** @var \Drupal\Core\Entity\Entity\EntityFormDisplay $entity_form_display */
      $entity_form_display = \Drupal::entityTypeManager()->getStorage('entity_form_display')->load($entity_type . '.' . $bundle . '.default');
      if (!$entity_form_display) {
        $values = array(
          'targetEntityType' => $entity_type,
          'bundle' => $bundle,
          'mode' => 'default',
          'status' => TRUE,
        );
        $entity_form_display = \Drupal::entityTypeManager()->getStorage('entity_form_display')->create($values);
      }
      $entity_form_display
        ->setComponent('field_site', array(
          'type' => 'entity_reference_autocomplete',
          'weight' => 40,
        ))
        ->removeComponent('field_site_page')
        ->removeComponent('field_site_page_type')
        ->save();

      //Tell the view system how to behave.
      /** @var \Drupal\Core\Entity\Entity\EntityViewDisplay $entity_view_display */
      $entity_view_display = \Drupal::entityTypeManager()->getStorage('entity_view_display')->load($entity_type . '.' . $bundle . '.default');
      if (!$entity_view_display) {
        $values = array(
          'targetEntityType' => $entity_type,
          'bundle' => $bundle,
          'mode' => 'default',
          'status' => TRUE,
        );

        $entity_view_display = \Drupal::entityTypeManager()->getStorage('entity_view_display')->create($values);
      }

      $entity_view_display
        ->removeComponent('field_site')
        ->removeComponent('field_site_page')
        ->removeComponent('field_site_page_type')
        ->save();
    }
    else {
      $field = FieldConfig::loadByName($entity_type, $bundle, 'field_site');
      if($field) {
        $query = \Drupal::entityQuery('node')->exists('field_site');
        $nodes_ids = $query->execute();
      }
      foreach ($fields as $currentField => $currentFieldConfig) {
        $field = FieldConfig::loadByName($entity_type, $bundle, $currentField);
        if (!empty($field)) {
          $field->delete();
        }
        //remove storage only if no more field exist.
        if (!empty($field_storage = FieldStorageConfig::loadByName($entity_type, $currentField)) && empty($field_storage->getBundles())) {
          $field_storage->delete();
        }
      }

      if(!empty($nodes_ids)) {
        //update mini site entity reference
        $fieldMap = \Drupal::service('entity_field.manager')->getFieldMapByFieldType('mini_site_entity_reference');
        array_walk($fieldMap['mini_site_page'], function($field, $field_name) use ($nodes_ids) {
          $query = \Drupal::entityQuery('mini_site_page');
          $query->condition('type', $field['bundles'], 'IN')
                ->condition($field_name, $nodes_ids, 'IN');
          $page_ids = $query->execute();
          $pages = MiniSitePage::loadMultiple($page_ids);
          foreach($pages as $currentPage) {
            $currentPage->{$field_name}->target_id = NULL;
            $currentPage->save();
          }
        });

        if(\Drupal::moduleHandler()->moduleExists('pathauto')) {
          $nodes = Node::loadMultiple($nodes_ids);
          foreach($nodes as $currentNode) {
            \Drupal::service('pathauto.generator')->updateEntityAlias($currentNode, 'update');
          }
        }
      }
    }
  }
}

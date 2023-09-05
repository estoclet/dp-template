<?php

namespace Drupal\icdc_node_weight_order\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\icdc_node_weight_order\IcdcNodeWeightManager;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides node overview form for a icdc node order module.
 *
 * @internal
 */
class IcdcNodeOrder extends FormBase {

  /**
   * The database connection.
   *
   * @var \Drupal\icdc_node_weight_order\IcdcNodeWeightManager
   */
  protected $manager;

  /**
   * Class constructor.
   */
  public function __construct(IcdcNodeWeightManager $manager) {
    $this->manager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
    // Load the service required to construct this class.
      $container->get('icdc_node_weight_order.node_weight_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'icdc_node_order';
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state, $idKeyword = NULL) {
    if(is_null($idKeyword)) {
      $form['fieldset-keywords'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Add keywords'),
      ];
      $form['fieldset-keywords']['keywords'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Add keywords'),
        '#required' => TRUE,
        '#element_validate' => ['::keywordsValidation']
      ];
      $form['fieldset-keywords']['add'] = [
        '#type' => 'submit',
        '#value' => t('Add'),
        '#submit' => ['::addKeywordsSubmit'],
      ];

      $form['table-keywords'] = [
        '#type' => 'table',
        '#header' => [
          $this->t('Keyword'),
          $this->t('Action'),
        ],
        '#empty' => $this->t('Sorry, There are no keywords!'),
      ];
      $keywords = $this->manager->getAllKeywords();
      foreach ($keywords as $row) {
        $form['table-keywords'][$row->id]['keyword'] = [
          '#markup' => $row->keywords,
        ];
        $form['table-keywords'][$row->id]['manage_' . $row->id] = [
          '#type' => 'dropbutton',
          '#links' => [
            'manage' => [
              'title' => $this->t('Manage'),
              'url' => Url::fromRoute('icdc_node_weight_order.admin_order_manage_keywords', ['idKeyword' => $row->id]),
            ],
            'delete' => [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('icdc_node_weight_order.admin_order_delete_keywords', ['idKeyword' => $row->id]),
            ],
          ],
        ];
      }
    }
    else {
      $form_state->set('idKeyword', $idKeyword);
      $form['fieldset-search'] = array(
        '#type' => 'fieldset',
        '#title' => $this->t('Add content'),
      );
      $form['fieldset-search']['node'] = array(
        '#type' => 'entity_autocomplete',
        '#target_type' => 'node',
        '#selection_handler' => 'icdc',
        '#selection_settings' => [
          'sort' => [
            'field' => 'title',
            'direction' => 'asc'
          ],
          'id_keywords' => $idKeyword,
        ],
        '#prefix' => '<div id="icdc-node-weight-search">',
        '#suffix' => '</div>',
        '#required' => TRUE,
      );
      $form['fieldset-search']['add'] = array(
        '#type' => 'submit',
        '#value' => t('Add'),
        '#submit' => ['::addNodeOrderSubmit'],
      );

      $form['table-node-header'] = [
        '#markup' => '<h2>' . $this->manager->getKeyword($idKeyword)->keywords . '</h2>'
      ];
      $form['table-node'] = [
        '#type' => 'table',
        '#header' => [
          $this->t('Title'),
          $this->t('Type'),
          $this->t('Weight'),
          $this->t('Action'),
        ],
        '#empty' => $this->t('Sorry, There are no items!'),
        '#tabledrag' => [
          [
            'action' => 'order',
            'relationship' => 'sibling',
            'group' => 'table-sort-weight',
          ],
        ]
      ];
      $keywords = $this->manager->getNodesOrderByKeyword($idKeyword);

      foreach($keywords as $row) {
        $form['table-node'][$row->nid]['#attributes']['class'][] = 'draggable';
        $form['table-node'][$row->nid]['#weight'] = $row->weight;

        // Some table columns containing raw markup.
        $form['table-node'][$row->nid]['title'] = [
          '#type' => 'link',
          '#url' => Url::fromRoute('entity.node.edit_form', ['node' => $row->nid], ['query' => ['destination' => Url::fromRoute('icdc_node_weight_order.admin_order_manage_keywords', ['idKeyword' => $idKeyword])->toString()]]),
          '#title' => $row->entity->getTitle(),
        ];
        $form['table-node'][$row->nid]['type'] = [
          '#markup' => $row->entity->type->entity->label(),
        ];
        $form['table-node'][$row->nid]['weight'] = [
          '#type' => 'weight',
          '#title' => $this->t('Weight for @title', ['@title' => $row->entity->getTitle()]),
          '#title_display' => 'invisible',
          '#default_value' => $row->weight,
          '#attributes' => ['class' => ['table-sort-weight']],
        ];
        $form['table-node'][$row->nid]['delete_' . $row->nid] = [
          '#type' => 'submit',
          '#value' => $this->t('Delete'),
          '#name' => 'delete_' . $row->nid,
          '#submit' => ['::deleteNodeOrderSubmit'],
          '#attributes' => [
            'data-node-id' => $row->nid,
          ],
          '#limit_validation_errors' => [],
        ];
      }
      $form['actions'] = [
        '#type' => 'actions',
      ];
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
        '#submit' => [
          '::submitForm',
        ],
        '#limit_validation_errors' => [['table-node']],
      ];
      $form['actions']['cancel'] = [
        '#type' => 'submit',
        '#value' => $this->t('Cancel'),
        '#submit' => [
          '::cancel',
        ],
        '#limit_validation_errors' => [],
      ];

    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function keywordsValidation($element, FormStateInterface $form_state) {
    if (!empty($element['#value']) && $this->manager->searchKeyword($element['#value']) !== FALSE) {
      $form_state->setError($element, t('@label keyword already exist', ['@label' => $element['#value']]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addKeywordsSubmit(array $form, FormStateInterface $form_state) {
    $idKeyword = $this->manager->addKeyword($form_state->getValue('keywords'));
    $form_state->setRedirect('icdc_node_weight_order.admin_order_manage_keywords', ['idKeyword' => $idKeyword]);
  }

  /**
   * {@inheritdoc}
   */
  public function addNodeOrderSubmit(array $form, FormStateInterface $form_state) {
    $idKeyword = $form_state->get('idKeyword');
    $nid = $form_state->getValue('node');

    $this->manager->addNodesOrder($idKeyword, $nid);

    $input = $form_state->getUserInput();
    unset($input['node']);
    $form_state->setUserInput($input)->setRebuild();
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function deleteNodeOrderSubmit(array &$form, FormStateInterface $form_state) {
    $nid = $form_state->getTriggeringElement()['#attributes']['data-node-id'];
    $idKeyword = $form_state->get('idKeyword');
    $this->manager->deleteNodesOrder($idKeyword, $nid);
    $input = $form_state->getUserInput();
    unset($input['node']);
    $form_state->setUserInput($input)->setRebuild();
  }

  /**
   * Form submission handler for the 'Return to' action.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function cancel(array &$form, FormStateInterface $form_state) {
    $idKeyword = $form_state->get('idKeyword');
    $input = $form_state->getUserInput();
    unset($input['node']);
    $form_state->setUserInput($input)->setRebuild();
  }

  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $idKeyword = $form_state->get('idKeyword');
    $values = $form_state->getValue('table-node', []);
    foreach ($values as $nid => $val) {
      $this->manager->updateNodesOrder($idKeyword, $nid, $val['weight']);
    }
    $input = $form_state->getUserInput();
    unset($input['node']);
    $form_state->setUserInput($input)->setRebuild();
  }
}

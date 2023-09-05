<?php

namespace Drupal\apercu_view_mode\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 * Implements an example form.
 */
class ApercuForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'apercu_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['node'] = array(
      '#title' => 'Titre du contenu',
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#required' => TRUE,
      '#weight' => 0
    );
    $form['actions'] = [
      '#type' => 'actions',
      '#weight' => 0
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
      '#button_type' => 'primary',
    ];
    if(($nodeId = $form_state->getValue('node')) && ($node = Node::load($nodeId))) {
      $form['apercu'] = [];
      $nodeEntity = \Drupal::service('entity_display.repository');
      $viewModeBundle = $nodeEntity->getViewModeOptionsByBundle('node', $node->getType());
      foreach ($viewModeBundle as $viewModeId => $viewMode) {
        $url = Url::fromRoute('apercu_view_mode.apercu', ['node' => $nodeId, 'viewMode' => $viewModeId])->toString();
        $form['apercu'][$viewModeId .'_h2'] = [
          '#markup' => "<h3 class='apercu-view-mode-h3'>Mode d'affichage <i>$viewMode</i></h3>",
        ];
        $form['apercu'][$viewModeId .'_iframe'] = [
          '#type' => 'inline_template',
          '#template' => '<iframe src="{{ url }}" class="apercu-view-mode-iframe"></iframe><hr class="apercu-view-mode-hr">',
          '#context' => [
            'url' => $url,
          ]
        ];
      }
      $form['#attached']['library'][] = 'apercu_view_mode/iframe_resizing';
    }
    return $form;
  }



  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild();
  }

}

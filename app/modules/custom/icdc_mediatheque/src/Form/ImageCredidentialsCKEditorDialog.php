<?php

namespace Drupal\icdc_mediatheque\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\BaseFormIdInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\node\Entity\Node;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\editor\Ajax\EditorDialogSave;
use Drupal\Core\Ajax\CloseModalDialogCommand;



/**
 * Class ImageCredidentialsCKEditorDialog.
 *
 * @package Drupal\icdc_mediatheque\Form
 */
class ImageCredidentialsCKEditorDialog extends FormBase implements BaseFormIdInterface {

       /**
   * {@inheritdoc}
   */
   public function getFormId() {
    return 'editor_image_credidentials_popup_dialog';
  }

    /**
   * {@inheritdoc}
   */
  public function getBaseFormId() {
    // Use the EditorLinkDialog form id to ease alteration.
    return 'editor_image_credidentials_popup_dialog_base';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    
    $form['#tree'] = TRUE;
    $form['#attached']['library'][] = 'editor/drupal.editor.dialog';
    $form['#prefix'] = '<div id="ckeditor-entity-link-dialog-form">';
    $form['#suffix'] = '</div>';

    $form['media'] = [
      '#type'       => 'textfield',
      '#title'      => 'Taper le nom du media',
      '#autocomplete_route_name' => 'icdc_mediatheque.autocomplete.image_credidentials',
      '#required'   => TRUE,
    ];

    $form['submit'] = [
      '#type'       => 'submit',
      '#input'      => 'TRUE',
      '#value'      => 'Insérer',
      '#submit' => [],
      '#ajax' => [
        'callback' => '::submitForm',
        'event' => 'click',
      ],
    ];

    return $form;
  }



   /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form,  FormStateInterface $form_state, director $director = NULL) {

  }
  /**
   * {@inheritdoc}
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function submitForm(array &$form, FormStateInterface $form_state){
    $values = $form_state->getValues();
    $credidential = '';
    $media_query = \Drupal::entityTypeManager()->getStorage('media')->getQuery()
      ->condition('bundle', 'image')
      ->condition('name', $values['media'])
      ->execute();

    $response = new AjaxResponse();

    if ($media_query > 0) {
      $media = \Drupal::service('entity_type.manager')->getStorage('media')->load(reset($media_query));
      $credidential = $media->get('field_media_credential')->value;
      $value_credit = [
        'attributes' => [
            'credit' => !empty($credidential) ? $credidential : '',
        ],
      ];
      
      $response->addCommand(new EditorDialogSave($value_credit));
      $response->addCommand(new CloseModalDialogCommand());
  }

    return $response;
  }
}

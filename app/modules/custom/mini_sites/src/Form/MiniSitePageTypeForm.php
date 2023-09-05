<?php

namespace Drupal\mini_sites\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class MiniSitePageTypeForm.
 */
class MiniSitePageTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $mini_site_page_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $mini_site_page_type->label(),
      '#description' => $this->t("Label for the Mini site page type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $mini_site_page_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\mini_sites\Entity\MiniSitePageType::load',
      ],
      '#disabled' => !$mini_site_page_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $mini_site_page_type = $this->entity;
    $status = $mini_site_page_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Mini site page type.', [
          '%label' => $mini_site_page_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Mini site page type.', [
          '%label' => $mini_site_page_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($mini_site_page_type->toUrl('collection'));
  }

}

<?php

namespace Drupal\mini_sites\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mini_sites\Entity\MiniSitePageType;

/**
 * Class MiniSiteTypeForm.
 */
class MiniSiteTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $mini_site_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $mini_site_type->label(),
      '#description' => $this->t("Label for the Mini site type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $mini_site_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\mini_sites\Entity\MiniSiteType::load',
      ],
      '#disabled' => !$mini_site_type->isNew(),
    ];

    $options = [];
    $miniSitePageTypes = MiniSitePageType::loadMultiple();
    foreach($miniSitePageTypes as $currentPageType) {
      $options[$currentPageType->id()] = $currentPageType->label();
    }
    $form['allow_page_type'] = [
      '#title' => $this->t('Page type allowed'),
      '#type' => 'checkboxes',
      '#default_value' => $mini_site_type->get('allow_page_type') ? $mini_site_type->get('allow_page_type') : [],
      '#options' => $options,
      '#description' => $this->t("Page types allowed. If empty, all page types will be available."),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $mini_site_type = $this->entity;
    $status = $mini_site_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Mini site type.', [
          '%label' => $mini_site_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Mini site type.', [
          '%label' => $mini_site_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($mini_site_type->toUrl('collection'));
  }

}

<?php

namespace Drupal\mini_sites\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a form for deleting Mini site page entities.
 *
 * @ingroup mini_sites
 */
class MiniSitePageDeleteForm extends ContentEntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function traitGetCancelUrl() {
    $entity = $this->getEntity();
    if ($entity->hasLinkTemplate('collection')) {
      // If available, return the collection URL.
      return Url::fromRoute('entity.mini_site_page.collection', ['mini_site' => $entity->get('mini_site')->target_id]);
    }
    else {
      // Otherwise fall back to the default link template.
      return $entity->toUrl();
    }
  }
  /**
   * Returns the URL where the user should be redirected after deletion.
   *
   * @return \Drupal\Core\Url
   *   The redirect URL.
   */
  protected function getRedirectUrl() {
    $entity = $this->getEntity();
    if ($entity->hasLinkTemplate('collection')) {
      // If available, return the collection URL.
      return Url::fromRoute('entity.mini_site_page.collection', ['mini_site' => $entity->get('mini_site')->target_id]);
    }
    else {
      // Otherwise fall back to the front page.
      return Url::fromRoute('<front>');
    }
  }

}

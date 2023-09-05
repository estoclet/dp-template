<?php

namespace Drupal\icdc_blog\Plugin\views\argument;

use Drupal\user\Plugin\views\argument\Uid as ArgumentBase;

/**
 * Defines a filter for User Slugs.
 *
 * @ingroup views_argument_handlers
 *
 * @ViewsArgument("custom_user_slug")
 */
class CustomUserSlug extends ArgumentBase {

  /**
   * {@inheritdoc}
   */
  public function setArgument($arg) {
    // If we are not dealing with the exception argument, example "all".
    if ($this->isException($arg)) {
      return parent::setArgument($arg);
    }
    $tid = is_numeric($arg)
      ? $arg : $this->convertSlugToUid($arg);
    $this->argument = (int) $tid;
    return $this->validateArgument($tid);
  }

  /**
   * Get User ID from a slug.
   *
   * @return int
   *   User ID.
   */
  protected function convertSlugToUid($slug) {
    if (strpos($slug, '_') === FALSE) {
      return FALSE;
    }
    list($firstName, $lastName) = explode('_', $slug);
    // Build query to get user.
    $query = $this->storage
      ->getQuery();
    if ($firstName) {
      $query->condition('field_user_firstname', $firstName);
    }
    if ($lastName) {
      $query->condition('field_user_lastname', $lastName);
    }
    $uids = $query->execute();


    return $uids ? reset($uids) : FALSE;
  }
}

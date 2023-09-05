<?php

namespace Drupal\icdc_blog\Plugin\views\argument_validator;

use Drupal\taxonomy\Plugin\views\argument_validator\TermName;

/**
 * Validates an argument as a term name and converts it to the term ID.
 *
 * @ViewsArgumentValidator(
 *   id = "icdc_blog_taxonomy_term_name_into_id",
 *   title = @Translation("ICDC Blog Taxonomy term name as ID"),
 *   entity_type = "taxonomy_term"
 * )
 */
class IcdcBlogTermNameAsId extends TermName {

  /**
   * {@inheritdoc}
   */
  public function validateArgument($argument) {
    $argument = $argument ? str_replace('-', '_', $argument): "";

    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('name', $argument, 'LIKE');
    // If bundles is set then restrict the loaded terms to the given bundles.
    if (!empty($this->options['bundles'])) {
      $query->condition('vid', $this->options['bundles']);
    }
    $tids = $query->execute();
    $terms = $this->termStorage->loadMultiple($tids);

    // $terms are already bundle tested but we need to test access control.
    foreach ($terms as $term) {
      if ($this->validateEntity($term)) {
        // We only need one of the terms to be valid, so set the argument to
        // the term ID return TRUE when we find one.
        $this->argument->argument = $term->id();
        return TRUE;
        // @todo: If there are other values in $terms, maybe it'd be nice to
        // warn someone that there were multiple matches and we're only using
        // the first one.
      }
    }
    return FALSE;
  }

}

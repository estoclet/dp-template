<?php

declare(strict_types = 1);

use drupol\PhpCsFixerConfigsDrupal\Config\Drupal8;
use Ely\CS\Fixers;

$custom_config = new Drupal8();

$custom_config->registerCustomFixers(new Fixers());

$custom_config->setCacheFile('/tmp/.php_cs.cache');

// Overrides rules provided by vendor.
$rules = $custom_config->getRules();

// @see https://cs.symfony.com/doc/rules/index.html.
$rules['blank_line_before_statement']['statements'] = [
  'case',
  'declare',
  'default',
];
$rules['declare_strict_types'] = TRUE;
$rules['doctrine_annotation_array_assignment']['operator'] = '=';
$rules['doctrine_annotation_spaces']['before_argument_assignments'] = TRUE;
$rules['doctrine_annotation_spaces']['after_argument_assignments'] = TRUE;
$rules['doctrine_annotation_spaces']['before_array_assignments_equals'] = TRUE;
$rules['doctrine_annotation_spaces']['after_array_assignments_equals'] = TRUE;
$rules['error_suppression'] = FALSE;
$rules['native_function_invocation']['include'] = [
  '@all',
];
$rules['no_superfluous_phpdoc_tags'] = FALSE;
$rules['ordered_class_elements'] = FALSE;
$rules['php_unit_internal_class'] = FALSE;
$rules['php_unit_test_case_static_method_calls']['call_type'] = 'this';
$rules['php_unit_test_class_requires_covers'] = FALSE;
$rules['strict_comparison'] = FALSE;

// "Contrib" rules.
$rules['Ely/blank_line_around_class_body'] = [
  'apply_to_anonymous_classes' => FALSE,
];
// Use Ely/blank_line_around_class_body instead, see https://github.com/drupol/phpcsfixer-configs-drupal/issues/7.
$rules['Drupal/blank_line_before_end_of_class'] = FALSE;

$custom_config->setRules($rules);

// @todo Files without .php extension are not detected.

return $custom_config;

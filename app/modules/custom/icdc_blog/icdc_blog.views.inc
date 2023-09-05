<?php

/**
 * @file
 * Provide views data for our custom module_name.
 */
/**
 * Implements hook_views_data().
 */
function icdc_blog_views_data() {
  $data['views']['author_area'] = array(
    'title' => t('Author area'),
    'help' => t('Provide a custom display for an author.'),
    'area' => array(
      'id' => 'author_area',
    ),
  );
  return $data;
}

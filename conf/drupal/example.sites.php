<?php

declare(strict_types = 1);

// phpcs:ignoreFile

$sites_env_mapping = [
  'DRUPAL_SITE_DEFAULT_DOMAIN_1' => 'DRUPAL_SITE_DEFAULT_FOLDER_NAME',
  'DRUPAL_SITE_DEFAULT_DOMAIN_2' => 'DRUPAL_SITE_DEFAULT_FOLDER_NAME',
  'DRUPAL_SITE_DEFAULT_DOMAIN_3' => 'DRUPAL_SITE_DEFAULT_FOLDER_NAME',
  'DRUPAL_SITE_DEFAULT_DOMAIN_1_VARNISH' => 'DRUPAL_SITE_DEFAULT_FOLDER_NAME',
  'DRUPAL_SITE_DEFAULT_DOMAIN_2_VARNISH' => 'DRUPAL_SITE_DEFAULT_FOLDER_NAME',
  'DRUPAL_SITE_DEFAULT_DOMAIN_3_VARNISH' => 'DRUPAL_SITE_DEFAULT_FOLDER_NAME',
];

foreach ($sites_env_mapping as $domain_env => $folder_env) {
  if (isset($_ENV[$domain_env])
    && !empty($_ENV[$domain_env])
    && isset($_ENV[$folder_env])
    && !empty($_ENV[$folder_env])
  ) {
    $sites[$_ENV[$domain_env]] = $_ENV[$folder_env];
  }
}

<?php

// phpcs:ignoreFile

declare(strict_types = 1);

use DrupalFinder\DrupalFinder;
use DrupalRector\Set\Drupal8SetList;
use DrupalRector\Set\Drupal9SetList;
use Rector\Config\RectorConfig;
use Rector\PHPUnit\Rector\Class_\AddProphecyTraitRector;
use Rector\PHPUnit\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector;

return static function (RectorConfig $rectorConfig): void {
  $rectorConfig->sets([
    Drupal8SetList::DRUPAL_8,
    Drupal9SetList::DRUPAL_9,
  ]);

  $parameters = $rectorConfig->parameters();

  $drupalFinder = new DrupalFinder();
  $drupalFinder->locateRoot(__DIR__);
  $drupalRoot = $drupalFinder->getDrupalRoot();
  $rectorConfig->autoloadPaths([
    $drupalRoot . '/core',
    $drupalRoot . '/modules',
    $drupalRoot . '/profiles',
    $drupalRoot . '/themes',
  ]);

  $rectorConfig->skip([
    AddDoesNotPerformAssertionToNonAssertingTestRector::class,
    AddProphecyTraitRector::class,
    // This path is used by the upgrade_status module.
    '*/upgrade_status/tests/modules/*',
    // If you would like to skip test directories, uncomment the following lines:
    // '*/tests/*',
    // '*/Tests/*',
  ]);

  $rectorConfig->fileExtensions([
    'engine',
    'inc',
    'install',
    'module',
    'php',
    'profile',
    'theme',
  ]);

  // Create `use` statements.
  $rectorConfig->importNames(FALSE);
  // Do not convert `\Drupal` to `Drupal`, etc.
  $rectorConfig->importShortClasses(FALSE);
  // This will add comments to call out edge cases in replacements.
  $parameters->set('drupal_rector_notices_as_comments', TRUE);
};

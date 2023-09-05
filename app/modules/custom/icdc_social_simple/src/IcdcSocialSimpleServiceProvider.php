<?php

namespace Drupal\icdc_social_simple;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Alters container services.
 */
class IcdcSocialSimpleServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    parent::alter($container);

    $container->getDefinition('social_simple.generator')
      ->setClass(IcdcSocialSimpleGenerator::class);
  }

}

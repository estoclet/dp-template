<?php

namespace Drupal\mini_sites\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Mini site entities.
 *
 * @ingroup mini_sites
 */
interface MiniSiteInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Mini site name.
   *
   * @return string
   *   Name of the Mini site.
   */
  public function getName();

  /**
   * Sets the Mini site name.
   *
   * @param string $name
   *   The Mini site name.
   *
   * @return \Drupal\mini_sites\Entity\MiniSiteInterface
   *   The called Mini site entity.
   */
  public function setName($name);

  /**
   * Gets the Mini site creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Mini site.
   */
  public function getCreatedTime();

  /**
   * Sets the Mini site creation timestamp.
   *
   * @param int $timestamp
   *   The Mini site creation timestamp.
   *
   * @return \Drupal\mini_sites\Entity\MiniSiteInterface
   *   The called Mini site entity.
   */
  public function setCreatedTime($timestamp);

}

<?php

namespace Drupal\mini_sites\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Mini site page entities.
 *
 * @ingroup mini_sites
 */
interface MiniSitePageInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Mini site page name.
   *
   * @return string
   *   Name of the Mini site page.
   */
  public function getName();

  /**
   * Sets the Mini site page name.
   *
   * @param string $name
   *   The Mini site page name.
   *
   * @return \Drupal\mini_sites\Entity\MiniSitePageInterface
   *   The called Mini site page entity.
   */
  public function setName($name);

  /**
   * Gets the Mini site page creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Mini site page.
   */
  public function getCreatedTime();

  /**
   * Sets the Mini site page creation timestamp.
   *
   * @param int $timestamp
   *   The Mini site page creation timestamp.
   *
   * @return \Drupal\mini_sites\Entity\MiniSitePageInterface
   *   The called Mini site page entity.
   */
  public function setCreatedTime($timestamp);

}

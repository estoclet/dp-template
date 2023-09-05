<?php

namespace Drupal\mini_sites\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Mini site type entity.
 *
 * @ConfigEntityType(
 *   id = "mini_site_type",
 *   label = @Translation("Mini site type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\mini_sites\MiniSiteTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\mini_sites\Form\MiniSiteTypeForm",
 *       "edit" = "Drupal\mini_sites\Form\MiniSiteTypeForm",
 *       "delete" = "Drupal\mini_sites\Form\MiniSiteTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\mini_sites\MiniSiteTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "mini_site_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "mini_site",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/mini-site/{mini_site_type}",
 *     "add-form" = "/admin/structure/mini-site/add",
 *     "edit-form" = "/admin/structure/mini-site/{mini_site_type}/edit",
 *     "delete-form" = "/admin/structure/mini-site/{mini_site_type}/delete",
 *     "collection" = "/admin/structure/mini-site"
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   }
 * )
 */
class MiniSiteType extends ConfigEntityBundleBase implements MiniSiteTypeInterface {

  /**
   * The Mini site type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Mini site type label.
   *
   * @var string
   */
  protected $label;

  /**
   * The Mini site type page type allowed.
   *
   * @var string[]
   */
  protected $allow_page_type;

}

<?php

namespace Drupal\mini_sites\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Mini site page type entity.
 *
 * @ConfigEntityType(
 *   id = "mini_site_page_type",
 *   label = @Translation("Mini site page type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\mini_sites\MiniSitePageTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\mini_sites\Form\MiniSitePageTypeForm",
 *       "edit" = "Drupal\mini_sites\Form\MiniSitePageTypeForm",
 *       "delete" = "Drupal\mini_sites\Form\MiniSitePageTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\mini_sites\MiniSitePageTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "mini_site_page_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "mini_site_page",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/mini-site-page/{mini_site_page_type}",
 *     "add-form" = "/admin/structure/mini-site-page/add",
 *     "edit-form" = "/admin/structure/mini-site-page/{mini_site_page_type}/edit",
 *     "delete-form" = "/admin/structure/mini-site-page/{mini_site_page_type}/delete",
 *     "collection" = "/admin/structure/mini-site-page"
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   }
 * )
 */
class MiniSitePageType extends ConfigEntityBundleBase implements MiniSitePageTypeInterface {

  /**
   * The Mini site page type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Mini site page type label.
   *
   * @var string
   */
  protected $label;

}

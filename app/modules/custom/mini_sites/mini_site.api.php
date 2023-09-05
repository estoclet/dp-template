<?php

/**
 * @param $contentPage array of page id and page type
 * @param $node \Drupal\node\Entity\Node
 *
 */
function hook_mini_site_find_page_alter(&$contentPage, $node) {

}

/**
 * @param $bundles array of mini site page bundle allowed
 * @param $miniSite \Drupal\mini_sites\Entity\MiniSite
 *
 */
function hook_mini_site_page_allowed_type_alter(&$bundles, $miniSite) {

}

/**
 * @param $linkObj \Drupal\Core\Link
 * @param $currentItem \stdClass
 * @param $active boolean
 *
 */
function hook_mini_site_page_menu_item_alter(&$linkObj, $currentItem, $active) {

}

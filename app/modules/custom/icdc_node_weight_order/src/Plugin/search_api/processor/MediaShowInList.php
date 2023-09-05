<?php

namespace Drupal\icdc_node_weight_order\Plugin\search_api\processor;

use Drupal\search_api\Processor\ProcessorPluginBase;

/**
 * Excludes media if needed in List.
 *
 * @SearchApiProcessor(
 *   id = "media_show_in_list",
 *   label = @Translation("ICDC Media Show In List index"),
 *   description = @Translation("Exclude media when not needed in List index."),
 *   stages = {
 *     "alter_items" = 0,
 *   },
 * )
 */
class MediaShowInList extends ProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function alterIndexedItems(array &$items) {
    // Annoyingly, this doc comment is needed for PHPStorm. See
    // http://youtrack.jetbrains.com/issue/WI-23586
    /** @var \Drupal\search_api\Item\ItemInterface $item */
    foreach ($items as $item_id => $item) {
      $object = $item->getOriginalObject()->getValue();
      $enabled = TRUE;
      if ($object->hasField('field_media_press_visibility')) {
        $enabled = $object->field_media_press_visibility->value == 1;
      }

      if (!$enabled) {
        unset($items[$item_id]);
      }
    }
  }

}

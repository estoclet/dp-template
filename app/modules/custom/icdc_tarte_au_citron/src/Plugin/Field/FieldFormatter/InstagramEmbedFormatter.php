<?php

namespace Drupal\icdc_tarte_au_citron\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\media_entity_instagram\Plugin\Field\FieldFormatter\InstagramEmbedFormatter as OldInstagramEmbedFormatter;

/**
 * Plugin implementation of the 'instagram_embed' formatter.
 *
 * @FieldFormatter(
 *   id = "instagram_embed",
 *   label = @Translation("Instagram embed"),
 *   field_types = {
 *     "link", "string", "string_long"
 *   }
 * )
 */
class InstagramEmbedFormatter extends OldInstagramEmbedFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = parent::viewElements($items, $langcode);

    $serviceManager = \Drupal::getContainer()->get('icdc_tarte_au_citron.services_manager');
    if (!empty($element['#attached']) && _icdc_tarte_au_citron_is_needed() && $serviceManager->isServiceEnabled('instagram')) {
      unset($element['#attached']);
    }

    return $element;
  }

}

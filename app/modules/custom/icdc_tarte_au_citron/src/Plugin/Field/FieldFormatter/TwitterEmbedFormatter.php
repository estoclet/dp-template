<?php

namespace Drupal\icdc_tarte_au_citron\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use \Drupal\media_entity_twitter\Plugin\Field\FieldFormatter\TwitterEmbedFormatter as OldTwitterEmbedFormatter;

/**
 * Plugin implementation of the 'twitter_embed' formatter.
 *
 * @FieldFormatter(
 *   id = "twitter_embed",
 *   label = @Translation("Twitter embed"),
 *   field_types = {
 *     "link", "string", "string_long"
 *   }
 * )
 */
class TwitterEmbedFormatter extends OldTwitterEmbedFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = parent::viewElements($items, $langcode);
    $serviceManager = \Drupal::getContainer()->get('icdc_tarte_au_citron.services_manager');
    if (!empty($element['#attached']) && _icdc_tarte_au_citron_is_needed() && $serviceManager->isServiceEnabled('twitter_drupal')) {
      unset($element['#attached']);
      foreach ($items as $delta => $item) {
        $element[$delta]['#attributes']['data-path'] = $element[$delta]['#path'];
      }
    }

    return $element;
  }

}

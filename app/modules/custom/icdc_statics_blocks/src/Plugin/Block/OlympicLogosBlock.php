<?php

namespace Drupal\icdc_statics_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'OlympicLogosBlock' block.
 *
 * @Block(
 *  id = "olympic_logos_block",
 *  admin_label = @Translation("Logos des JO 2024"),
 * )
 */
class OlympicLogosBlock extends BlockBase
{

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $build = [];
        $image_path = \Drupal::service('file_url_generator')->generateString('public://logo_olympic.png');
        $image = '<img src="' . $image_path . '" alt="logo des jeux olympiques" />';
        $build['#theme'] = 'olympic_logos_block';
        $build['olympic_logos_block']['#markup'] = $image;

        return $build;
    }

}


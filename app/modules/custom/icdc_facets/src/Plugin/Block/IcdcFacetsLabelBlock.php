<?php

namespace Drupal\icdc_facets\Plugin\Block;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\facets\Entity\Facet;
use Drupal\facets\Entity\FacetSource;
use Drupal\facets_summary\Entity\FacetsSummary;
use Drupal\facets_summary\Plugin\Block\FacetsSummaryBlock;

/**
 * Provides a 'Icdc Facets Label' block.
 *
 * @Block(
 *  id = "icdc_facets_label_block",
 *  category = "ICDC",
 *  admin_label = @Translation("FACETTE Etiquettes"),
 * )
 */
class IcdcFacetsLabelBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $currentRequest = \Drupal::request();
    $f = $currentRequest->query->get('f', []);
    array_walk($f, function(&$item) {
      $item = Xss::filter($item);
    });
    $items = [];
    if(is_array($f)) {
      foreach ($f as $currentF) {
        list($facetId, $facetValue) = explode(':', $currentF);
        $url = Url::createFromRequest($currentRequest);
        $options = array_values(array_diff($f, [$currentF]));
        $urlParams = [];
        if(!empty($currentRequest->query->all())) {
          $urlParams = $currentRequest->query->all();
          if(isset($urlParams['page'])) {
            unset($urlParams['page']);
          }
        }

        if(!empty($options)) {
          $urlParams['f'] = $options;
        }
        else if(isset($urlParams['f'])) {
          unset($urlParams['f']);
        }

        if(!empty($urlParams)) {
          $url->setOption('query', $urlParams);
        }
        $items[] = [
          '#theme' => 'icdc_facets_block_item',
          '#label' => $facetValue,
          '#url' => $url
        ];
      }
    }
    $globalUrl = Url::createFromRequest($currentRequest);
    return [
      '#theme' => 'icdc_facets_block_list',
      '#items' => $items,
      '#title' => '',
      '#clear_title' => $this->t('Remove filters'),
      '#url' => $globalUrl,
      '#list_type' => 'ul',
      '#wrapper_attributes' => [],
      '#attributes' => []
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    // see Drupal\facets\Plugin\Block\FacetBlock::getCacheMaxAge()
    return 0;
  }

}

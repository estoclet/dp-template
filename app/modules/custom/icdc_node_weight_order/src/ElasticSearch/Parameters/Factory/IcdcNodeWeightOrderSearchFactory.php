<?php

namespace Drupal\icdc_node_weight_order\ElasticSearch\Parameters\Factory;

use Drupal\elasticsearch_connector\ElasticSearch\Parameters\Factory\SearchFactory;
use Drupal\icdc_node_weight_order\ElasticSearch\Parameters\Builder\IcdcNodeWeightOrderSearchBuilder;
use Drupal\search_api\Query\QueryInterface;

/**
 * Class SearchFactory.
 */
class IcdcNodeWeightOrderSearchFactory extends SearchFactory {

  /**
   * Build search parameters from a query interface.
   *
   * @param \Drupal\search_api\Query\QueryInterface $query
   *   Search API query object.
   *
   * @return array
   *   Array of parameters to send along to the Elasticsearch _search endpoint.
   */
  public static function search(QueryInterface $query) {
    $builder = new IcdcNodeWeightOrderSearchBuilder($query);

    return $builder->build();
  }

}

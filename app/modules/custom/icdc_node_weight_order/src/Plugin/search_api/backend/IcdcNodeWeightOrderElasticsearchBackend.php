<?php

namespace Drupal\icdc_node_weight_order\Plugin\search_api\backend;

use Drupal\elasticsearch_connector\Plugin\search_api\backend\SearchApiElasticsearchBackend;
use Drupal\icdc_node_weight_order\ElasticSearch\Parameters\Factory\IcdcNodeWeightOrderSearchFactory;
use Drupal\search_api\Query\QueryInterface;

/**
 * Elasticsearch Search API Backend definition.
 *
 * @SearchApiBackend(
 *   id = "icdc_node_weight_order_elasticsearch",
 *   label = @Translation("ICDC Node Weight Order Elasticsearch"),
 *   description = @Translation("ICDC Node Weight Order Elasticsearch server.")
 * )
 */
class IcdcNodeWeightOrderElasticsearchBackend extends SearchApiElasticsearchBackend {

  /**
   * {@inheritdoc}
   */
  public function search(QueryInterface $query) {
    // Results.
    $search_result = $query->getResults();

    // Get index.
    $index = $query->getIndex();

    $params = $this->indexFactory->index($index, TRUE);

    // Check Elasticsearch index.
    if (!$this->client->indices()->exists($params)) {
      return $search_result;
    }

    // Add the facets to the request.
    if ($query->getOption('search_api_facets')) {
      $this->addFacets($query);
    }

    // Build Elasticsearch query.
    $params = IcdcNodeWeightOrderSearchFactory::search($query);

    // Note that this requires fielddata option to be enabled.
    // @see ::getAutocompleteSuggestions()
    // @see \Drupal\elasticsearch_connector\ElasticSearch\Parameters\Factory\IndexFactory::mapping()
    if ($incomplete_key = $query->getOption('autocomplete')) {
      // Autocomplete suggestions are determined using a term aggregation (like
      // facets), but filtered to only include facets with the right prefix.
      // As the search facet field is analyzed, facets are tokenized terms and
      // all in lower case. To match that, we need convert the our filter to
      // lower case also.
      $incomplete_key = strtolower($incomplete_key);
      // Note that we cannot use the elasticsearch client aggregations API as
      // it does not support the "include" parameter.
      $params['body']['aggs']['autocomplete']['terms'] = [
        'field' => $query->getOption('autocomplete_field'),
        'include' => $incomplete_key . '.*',
      ];
    }

    try {
      // Allow modules to alter the Elasticsearch query.
      $this->preQuery($query);

      // Do search.
      $response = $this->client->search($params)->getRawResponse();
      $results = IcdcNodeWeightOrderSearchFactory::parseResult($query, $response);

      // Handle the facets result when enabled.
      if ($query->getOption('search_api_facets')) {
        $this->parseFacets($results, $query);
      }

      // Allow modules to alter the Elasticsearch Results.
      $this->postQuery($results, $query, $response);

      return $results;
    }
    catch (\Exception $e) {
      watchdog_exception('Elasticsearch API', $e);
      return $search_result;
    }
  }
}

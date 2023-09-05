<?php

namespace Drupal\icdc_node_weight_order\ElasticSearch\Parameters\Builder;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\elasticsearch_connector\ElasticSearch\Parameters\Builder\SearchBuilder;
use Drupal\elasticsearch_connector\ElasticSearch\Parameters\Factory\FilterFactory;
use Drupal\search_api\ParseMode\ParseModeInterface;
use Drupal\search_api\Query\Condition;
use Drupal\search_api\Query\ConditionGroupInterface;
use Drupal\search_api\Query\QueryInterface;
use Elasticsearch\Common\Exceptions\ElasticsearchException;
use MakinaCorpus\Lucene\Query;
use MakinaCorpus\Lucene\TermCollectionQuery;
use MakinaCorpus\Lucene\TermQuery;
use Drupal\elasticsearch_connector\Event\PrepareSearchQueryEvent;
use Drupal\elasticsearch_connector\Event\BuildSearchParamsEvent;

/**
 * Class SearchBuilder.
 */
class IcdcNodeWeightOrderSearchBuilder extends SearchBuilder {

  /**
   * Helper function that returns sort for query in search.
   *
   * @return array
   *   Sort portion of the query.
   *
   * @throws \Exception
   */
  protected function getSortSearchQuery() {
    $sort = parent::getSortSearchQuery();
    if($options = $this->query->getOption('icdc_node_weight_order')) {
      $index_fields = $this->index->getFields();
      foreach($options['fields'] as $fieldId) {
        if(isset($index_fields[$fieldId]) && isset($sort[$fieldId]) && $index_fields[$fieldId]->getType() === 'object') {
          $pos = array_search($fieldId, array_keys($sort));
          $sort = array_slice($sort, 0, $pos) + [
              $fieldId . '.weight' => [
                'missing' => '_last',
                'order' => $sort[$fieldId],
                'nested' => [
                  'path' => $fieldId,
                  'filter' => [
                    'term' => [
                      $fieldId . '.keywords' => $options['search_value']
                    ]
                  ]
                ]
              ]
            ] + array_slice($sort, $pos + 1, count($sort));
        }
      }
    }
    return $sort;
  }

  /**
   * @inheritDoc
   */
  protected function getSearchQueryOptions() {
    $elasticSearchQuery = parent::getSearchQueryOptions();

    if($options = $this->query->getOption('icdc_node_weight_filter')) {
      $nestedCond = [
        'nested' => [
          'path' => $options['fields'],
          'query' => [
            'query_string' => [
              'fields' => [],
              'query' => $options['search_value']
            ]
          ]
        ]
      ];
      foreach($options['fields'] as $fieldId) {
        $nestedCond['nested']['query']['query_string']['fields'][] = $fieldId . '.keywords';
      }

      if(isset($elasticSearchQuery['query_search_string'])) {
        $elasticSearchQuery['query_search_string'] = [
          'bool' => [
            'should' => [
              $elasticSearchQuery['query_search_string'],
              $nestedCond
            ]
          ]
        ];
      }
      else {
        $elasticSearchQuery['query_search_string'] = $nestedCond;
      }
    }

    return $elasticSearchQuery;
  }

}

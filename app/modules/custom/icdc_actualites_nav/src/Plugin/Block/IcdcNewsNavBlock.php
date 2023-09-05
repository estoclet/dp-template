<?php

namespace Drupal\icdc_actualites_nav\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This block is used to display ICDC News Navigation block.
 *
 * @Block(
 *  id = "icdc_news_nav_block",
 *  admin_label = @Translation("ICDC News Navigation Block"),
 *  category = @Translation("ICDC")
 * )
 */
class IcdcNewsNavBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Active database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The entity view builder.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $view_builder;

  /**
   * The entity view builder.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\Core\Database\Connection $database
   *   The connection database.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $database, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->database = $database;
    $this->view_builder = $entity_type_manager->getViewBuilder('node');
    $this->storage = $entity_type_manager->getStorage('node');
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = \Drupal::routeMatch()->getParameter('node');
    if(!$node || !($node instanceof NodeInterface && $node->bundle() === 'actualite')) {
      return [];
    }

    $nextOrPrev = $this->generateNextPrevious($node);

    $build = [
      '#theme' => 'icdc_actualites_nav'
    ];

    if($nextOrPrev['previous'] || $nextOrPrev['next']) {
      $build['#title'] = [
        '#markup' => $this->t('Read also')
      ];
    }

    if($nextOrPrev['previous']) {
      $build['#prev'] =  $this->view_builder->view($nextOrPrev['previous'], 'bounce');
      $build['#prev_url'] = $nextOrPrev['previous']->toLink()->getUrl()->toString();
    }

    if($nextOrPrev['next']) {
      $build['#next'] =  $this->view_builder->view($nextOrPrev['next'], 'bounce');
      $build['#next_url'] = $nextOrPrev['next']->toLink()->getUrl()->toString();
    }

    return $build;
  }

  /**
   * Lookup the next or previous node
   * @param  NodeInterface $node a node
   * @return Node[] with 2 entries prev or next
   *
   */
  protected function generateNextPrevious($node) {
    $refQueryStr = 'SELECT nd.entity_id FROM node__field_interval_date nd LEFT JOIN node__field_site ns ON nd.entity_id = ns.entity_id WHERE ns.entity_id IS NULL AND nd.bundle LIKE :bundle_value AND nd.langcode = :langcode ORDER BY nd.field_interval_date_value ASC, nd.field_interval_date_end_value ASC, nd.entity_id ASC';
    $selectStr = "SELECT final.* FROM (SELECT @i:=@i+1 AS position, source.entity_id FROM ($refQueryStr) source, (SELECT @i:=0) b) final WHERE final.entity_id = :entity_id_value";
    $positionQuery = $this->database->query($selectStr, [
      ':bundle_value' => 'actualite',
      ':langcode' => $node->language()->getId(),
      ':entity_id_value' => (int) $node->id()
    ]);

    $ret = [
      'previous' => FALSE,
      'next' => FALSE
    ];

    if($positionQuery) {
      $posRes = $positionQuery->fetch();
      $selectStr = "SELECT final.* FROM (SELECT @i:=@i+1 AS position, source.entity_id FROM ($refQueryStr) source, (SELECT @i:=0) b) final WHERE final.position IN (:position_value_prev, :position_value_next)";
      $nodeQuery = $this->database->query($selectStr, [
        ':bundle_value' => 'actualite',
        ':langcode' => $node->language()->getId(),
        ':position_value_prev' =>  ((int) $posRes->position)-1,
        ':position_value_next' =>  ((int) $posRes->position)+1
      ]);

      if($nodeQuery) {
        $nodeRes = $nodeQuery->fetchAll();
        foreach($nodeRes as $currentNode) {
          if((int) $currentNode->position < (int) $posRes->position) {
            $ret['previous'] = $this->storage->load((int) $currentNode->entity_id);
          }
          else {
            $ret['next'] = $this->storage->load((int) $currentNode->entity_id);
          }
        }
      }
    }

    return $ret;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), ['node_type:actualite']);
  }

  public function getCacheContexts() {
    //need to be sure of active item link
    return Cache::mergeContexts(parent::getCacheContexts(), array('url.path'));
  }
}

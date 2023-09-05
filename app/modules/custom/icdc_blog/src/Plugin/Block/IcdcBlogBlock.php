<?php

namespace Drupal\icdc_blog\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\icdc_blog\Twig\IcdcBlogTermName;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * This block is used to display ICDC Blog.
 *
 * @Block(
 *  id = "icdc_blog",
 *  admin_label = @Translation("ICDC Blog"),
 *  category = @Translation("ICDC")
 * )
 */
class IcdcBlogBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The request object.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a new BookNavigationBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RequestStack $request_stack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    if ($node = $this->requestStack->getCurrentRequest()->get('node')) {
      return [];
    }

    $database = \Drupal::database();
    /**
     * @var $sth \Drupal\Core\Database\Query\Select
     */
    $sth = $database->select('taxonomy_term_field_data', 't');
    $sth->fields('t', ['tid'])
      ->groupBy('t.tid')
      ->join('node__field_expertise_references', 'nfe', 't.tid = nfe.field_expertise_references_target_id AND nfe.deleted = \'0\'');
    $sth->condition('t.vid', 'expertise_area')
      ->condition('t.status', 1);
    $data = $sth->execute();
    $results = array_keys($data->fetchAllKeyed(0, 0));

    $current_path = \Drupal::service('path.current')->getPath();
    $blogUri = Url::fromUri('internal:/blog');
    $activeOptions = [];;
    if($current_path === '/blog') {
      $activeOptions['attributes'] = [
        'title' => t('All') . ' - ' . t('active section'),
      ];
      $activeOptions['attributes'] += [
        'id' => ['all'],
      ];
      $activeOptions['attributes'] += [
        'class' => ['active-trail'],
      ];
      $blogUri->setOptions($activeOptions);
    }
    $blogLink = Link::fromTextAndUrl(t('All'), $blogUri);
    $items = [$blogLink->toRenderable()];

    $terms = Term::loadMultiple($results);
    foreach($terms as $currentTerm) {
      $termUri = Url::fromUri('internal:/blog/' . IcdcBlogTermName::termNameToUrl($currentTerm->label()));
      $id = str_replace(' ', '-',strtolower($currentTerm->label()));
      $activeOptions = [
        'attributes' => [
          'id' => $id
        ]
      ];
      $termUri->setOptions($activeOptions);

      if ('/blog/' . IcdcBlogTermName::termNameToUrl($currentTerm->label()) === $current_path) {
        $activeOptions['attributes'] += [
            'title' => $currentTerm->label() . ' - ' . t('active section')
        ];
        $activeOptions['attributes'] += [
          'class' => ['active-trail'],
        ];
        $termUri->setOptions($activeOptions);
      }
      $termLink = Link::fromTextAndUrl($currentTerm->getName(), $termUri);
      $items[] = $termLink->toRenderable();
    }
    $url = \Drupal::request()->getSchemeAndHttpHost().'/blog/auteurs';
    $build = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => $items,
      '#prefix' => '<div class="view-blog"><div class="menu--menu-blog">',
      '#suffix' => '<li class="all-authors-item"><a href='.$url.' class="all-authors" id="tous-les-auteurs">Tous les auteurs</a></li></div></div>'
    ];

    return $build;
  }

  public function getCacheContexts() {
    //need to be sure of active item link
    return Cache::mergeContexts(parent::getCacheContexts(), array('url.path'));
  }

}

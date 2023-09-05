<?php

namespace Drupal\mini_sites\Plugin\Condition;
use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mini_sites\Entity\MiniSite;
use Drupal\mini_sites\Entity\MiniSitePage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an 'Mini site page content' condition.
 *
 * @Condition(
 *   id = "mini_site_page_content",
 *   label = @Translation("Mini Site Content"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Node"))
 *   }
 * )
 */
class MiniSitePageContent extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityStorage;

  /**
   * Creates a new NodeType instance.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The entity storage.
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(EntityStorageInterface $entity_storage, array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityStorage = $entity_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('entity_type.manager')->getStorage('mini_site_page_type'),
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['mini_site_page_content'] = [
      '#title' => $this->t('Content relates to a mini site page'),
      '#type' => 'radios',
      '#options' => [
        'na' => $this->t('N/A'),
        'no' => $this->t('No'),
        'yes' => $this->t('Yes')
      ],
      '#default_value' => !empty($this->configuration['mini_site_page_content']) ? $this->configuration['mini_site_page_content'] : 'na'
    ];

    $options = [];
    $mini_site_page_types = $this->entityStorage->loadMultiple();
    foreach ($mini_site_page_types as $type) {
      $options[$type->id()] = $type->label();
    }
    $form['mini_site_page_content_bundles'] = [
      '#title' => $this->t('Mini Site Page types'),
      '#type' => 'checkboxes',
      '#options' => $options,
      '#default_value' => $this->configuration['mini_site_page_content_bundles'],
      '#states' => array(
        'visible' => array(
          ':input[name="mini_site_page_content"]' => array('value' => 'yes'),
        ),
      ),
    ];

    $form['mini_site_page_content_home'] = [
      '#title' => $this->t('Content is homepage of a mini site page'),
      '#type' => 'radios',
      '#options' => [
        'no' => $this->t('No'),
        'yes' => $this->t('Yes')
      ],
      '#default_value' => !empty($this->configuration['mini_site_page_content_home']) ? $this->configuration['mini_site_page_content_home'] : 'no',
      '#states' => array(
        'visible' => array(
          ':input[name="mini_site_page_content"]' => array('value' => 'yes'),
        ),
      ),
    ];

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['mini_site_page_content'] = $form_state->getValue('mini_site_page_content');
    $this->configuration['mini_site_page_content_bundles'] = array_filter($form_state->getValue('mini_site_page_content_bundles', []));
    $this->configuration['mini_site_page_content_home'] = $form_state->getValue('mini_site_page_content_home');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    if(!empty($this->configuration['mini_site_page_content']) && $this->configuration['mini_site_page_content'] == 'yes') {
      if (count($this->configuration['mini_site_page_content_bundles']) > 1) {
        $bundles = $this->configuration['mini_site_page_content_bundles'];
        $last = array_pop($bundles);
        $bundles = implode(', ', $bundles);
        if(!empty($this->configuration['mini_site_page_content_home']) && $this->configuration['mini_site_page_content_home'] == 'yes') {
          return $this->t('The content is linked to a mini site, is the homepage, and page bundle is @bundles or @last', ['@bundles' => $bundles, '@last' => $last]);
        }
        return $this->t('The content is linked to a mini site and page bundle is @bundles or @last', ['@bundles' => $bundles, '@last' => $last]);
      }
      if(!empty($this->configuration['mini_site_page_content_home']) && $this->configuration['mini_site_page_content_home'] == 'yes') {
        return $this->t('The content is linked to a mini site and is the homepage');
      }
      return $this->t('The content is linked to a mini site');
    }
    return $this->t('The content is not linked to a mini site');
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    if(empty($this->configuration['mini_site_page_content']) || $this->configuration['mini_site_page_content'] == 'na') {
      return TRUE;
    }

    $node = $this->getContextValue('node');
    $miniSite = $this->getMiniSite($node);
    if (!$miniSite && $this->configuration['mini_site_page_content'] === 'no') {
      return TRUE;
    }

    return $miniSite && $this->configuration['mini_site_page_content'] === 'yes' &&
      (
        empty($this->configuration['mini_site_page_content_bundles']) ||
        (($miniSitePage = $this->getMiniSitePage($node)) !== FALSE && !empty($this->configuration['mini_site_page_content_bundles'][$miniSitePage->type->target_id]))
      ) &&
      (
        empty($this->configuration['mini_site_page_content_home']) || ($this->configuration['mini_site_page_content_home'] == 'no' && $miniSite->home->target_id != $node->id()) || ($this->configuration['mini_site_page_content_home'] == 'yes' && $miniSite->home->target_id == $node->id())
      );
  }

  protected function getMiniSite($node) {
    $config = \Drupal::config('mini_sites.settings');
    if(!in_array($node->getType(), $config->get('node_type_field_site.node_types') ?:[]) || !$node->hasField('field_site') || empty($node->field_site->target_id)) {
      return FALSE;
    }

    return MiniSite::load($node->field_site->target_id);
  }

  protected function getMiniSitePage($node) {
    /**
     * @var \Drupal\mini_sites\Entity\MiniSitePage $miniSitePage
     */
    $miniSitePage =  !empty($node->field_site_page) && !empty($node->field_site_page->target_id) ? MiniSitePage::load($node->field_site_page->target_id) : FALSE;

    return $miniSitePage;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'mini_site_page_content' => 'na',
      'mini_site_page_content_home' => 'no',
      'mini_site_page_content_bundles' => []
    ] + parent::defaultConfiguration();
  }
}

<?php

namespace Drupal\mini_sites\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\user\UserInterface;
use Drupal\node\NodeInterface;

/**
 * Defines the Mini site entity.
 *
 * @ingroup mini_sites
 *
 * @ContentEntityType(
 *   id = "mini_site",
 *   label = @Translation("Mini site"),
 *   bundle_label = @Translation("Mini site type"),
 *   handlers = {
 *     "storage" = "Drupal\mini_sites\MiniSiteStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\mini_sites\MiniSiteListBuilder",
 *     "views_data" = "Drupal\mini_sites\Entity\MiniSiteViewsData",
 *     "translation" = "Drupal\mini_sites\MiniSiteTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\mini_sites\Form\MiniSiteForm",
 *       "add" = "Drupal\mini_sites\Form\MiniSiteForm",
 *       "edit" = "Drupal\mini_sites\Form\MiniSiteForm",
 *       "delete" = "Drupal\mini_sites\Form\MiniSiteDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\mini_sites\MiniSiteHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\mini_sites\MiniSiteAccessControlHandler",
 *   },
 *   base_table = "mini_site",
 *   data_table = "mini_site_field_data",
 *   translatable = TRUE,
 *   permission_granularity = "bundle",
 *   admin_permission = "administer mini site entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *     "menu_type" = "menu_type",
 *     "home" = "home"
 *   },
 *   links = {
 *     "canonical" = "/mini-site/{mini_site}",
 *     "add-page" = "/mini-site/add",
 *     "add-form" = "/mini-site/add/{mini_site_type}",
 *     "edit-form" = "/mini-site/{mini_site}/edit",
 *     "delete-form" = "/mini-site/{mini_site}/delete",
 *     "collection" = "/mini-site",
 *   },
 *   bundle_entity_type = "mini_site_type",
 *   field_ui_base_route = "entity.mini_site_type.edit_form"
 * )
 */
class MiniSite extends ContentEntityBase implements MiniSiteInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getMenuType() {
    return $this->get('menu_type')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setMenuType($menu_type) {
    $this->set('menu_type', $menu_type);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Mini site entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'hidden',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Mini site entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'type' => 'hidden',
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['status']->setDescription(t('A boolean indicating whether the Mini site is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['menu_type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Menu Type'))
      ->setDescription(t('How the mini site menu will be rendered.'))
      ->setDefaultValue('basic')
      ->setRequired(TRUE)
      ->setSetting('allowed_values', ['basic' => t('Basic'), 'anchor' => t('Anchor')])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => -2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setRequired(TRUE);

    $fields['home'] = BaseFieldDefinition::create('mini_site_entity_reference')
      ->setLabel(t('Homepage'))
      ->setDescription(t('The homepage of this site.'))
      ->setSetting('target_type', 'node')
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'mini_site_entity_reference_autocomplete',
      ])
      ->setDisplayConfigurable('form', TRUE);
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);

    /** @var $storage \Drupal\mini_sites\MiniSiteStorage */
    foreach ($entities as $entity) {
      $pages = $storage->getPages($entity);
      foreach ($pages as $currentPage) {
        if($miniSitePage = MiniSitePage::load($currentPage->id)) {
          $miniSitePage->delete();
        }
      }
      $query = \Drupal::entityQuery('node')->condition('field_site', $entity->id());
      $result = $query->execute();
      if ($result) {
        $nodes_to_update = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($result);
        foreach ($nodes_to_update as $node) {
          /** @var $node \Drupal\node\Entity\Node */
          $node->set('field_site', [
            'target_id' => NULL
          ]);
          $node->save();
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function toUrl($rel = 'canonical', array $options = []) {
    if ($rel === 'revision' && $this instanceof RevisionableInterface && $this->isDefaultRevision()) {
      $rel = 'canonical';
    }
    $link_templates = $this->linkTemplates();
    if($rel == 'canonical' && isset($link_templates[$rel])) {
      $route_name = "entity.node." . str_replace(['-', 'drupal:'], ['_', ''], $rel);
      $uri = new Url($route_name, ['node' => $this->home->target_id]);
      $uri
        ->setOption('entity_type', 'node')
        ->setOption('entity', Node::load($this->home->target_id))
        ->setOption('language', $this->language());
      return $uri;
    }
    else {
      return parent::toUrl($rel, $options);
    }
  }
}

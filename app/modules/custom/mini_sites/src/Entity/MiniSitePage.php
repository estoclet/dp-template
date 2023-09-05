<?php

namespace Drupal\mini_sites\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Mini site page entity.
 *
 * @ingroup mini_sites
 *
 * @ContentEntityType(
 *   id = "mini_site_page",
 *   label = @Translation("Mini site page"),
 *   bundle_label = @Translation("Mini site page type"),
 *   handlers = {
 *     "storage" = "Drupal\mini_sites\MiniSitePageStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\mini_sites\MiniSitePageListBuilder",
 *     "views_data" = "Drupal\mini_sites\Entity\MiniSitePageViewsData",
 *     "translation" = "Drupal\mini_sites\MiniSitePageTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\mini_sites\Form\MiniSitePageForm",
 *       "add" = "Drupal\mini_sites\Form\MiniSitePageForm",
 *       "edit" = "Drupal\mini_sites\Form\MiniSitePageForm",
 *       "delete" = "Drupal\mini_sites\Form\MiniSitePageDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\mini_sites\MiniSitePageHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\mini_sites\MiniSitePageAccessControlHandler",
 *   },
 *   base_table = "mini_site_page",
 *   data_table = "mini_site_page_field_data",
 *   translatable = TRUE,
 *   permission_granularity = "bundle",
 *   admin_permission = "administer mini site page entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/mini-site-page/{mini_site_page}",
 *     "add-page" = "/mini-site-page/{mini_site}/add",
 *     "add-form" = "/mini-site-page/{mini_site}/add/{mini_site_page_type}",
 *     "edit-form" = "/mini-site-page/{mini_site_page}/edit",
 *     "delete-form" = "/mini-site-page/{mini_site_page}/delete",
 *     "collection" = "/mini-site/{mini_site}/pages",
 *   },
 *   bundle_entity_type = "mini_site_page_type",
 *   field_ui_base_route = "entity.mini_site_page_type.edit_form"
 * )
 */
class MiniSitePage extends ContentEntityBase implements MiniSitePageInterface {

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
  public function getWeight() {
    return $this->get('weight')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->set('weight', $weight);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getMiniSite() {
    return MiniSite::load($this->mini_site->target_id);
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    // Pages with no parents are mandatory children of <root>.
    if (!$this->get('parent')->count()) {
      $this->parent->target_id = 0;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);

    $orphans = [];
    /** @var \Drupal\mini_sites\Entity\MiniSitePageInterface $page */
    foreach ($entities as $tid => $page) {
      if ($children = $storage->getChildren($page)) {
        /** @var \Drupal\mini_sites\Entity\MiniSitePageInterface $child */
        foreach ($children as $child) {
          $parent = $child->get('parent');
          // Update child parents item list.
          $parent->filter(function ($item) use ($tid) {
            return $item->target_id != $tid;
          });

          // If the term has multiple parents, we don't delete it.
          if ($parent->count()) {
            $child->save();
          }
          else {
            $orphans[] = $child;
          }
        }
      }
    }

    if (!empty($orphans)) {
      $storage->delete($orphans);
    }
  }
  /**
   * {@inheritdoc}
   */
  public static function bundleFieldDefinitions(EntityTypeInterface $entity_type, $bundle, array $base_field_definitions) {
    // Only terms in the same bundle can be a parent.
    $fields['parent'] = clone $base_field_definitions['parent'];
    $fields['parent']->setSetting('handler_settings', ['target_bundles' => [$bundle => $bundle]]);
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['mini_site'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Mini Site'))
      ->setDescription(t('The mini site of this page.'))
      ->setSetting('target_type', 'mini_site');

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Mini site page entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
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
      ->setDescription(t('The name of the Mini site page entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['status']->setDescription(t('A boolean indicating whether the Mini site page is published.'))
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

    $fields['weight'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Weight'))
      ->setDescription(t('The weight of this page in relation to other pages.'))
      ->setDefaultValue(0);

    $fields['parent'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Page Parents'))
      ->setDescription(t('The parents of this page.'))
      ->setSetting('target_type', 'mini_site_page');
    return $fields;
  }

}

<?php

namespace Drupal\icdc_blog\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Allows you to manually configure the authors visible in the 'They publish' block.
 *
 * @internal
 */
class IcdcTheyPublish extends ConfigFormBase {

  const THEY_PUBLISH_SETTINGS_ID = "icdc_blog_settings_form";
  const CONFIG_NAME = 'icdc_blog.settings';
  const AUTHORS_NUMBER = 4;

  protected $entityTypeManager;

  /**
   * IcdcTheyPublish constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config manager service interface.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return self::THEY_PUBLISH_SETTINGS_ID;
  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return [
      self::CONFIG_NAME,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(self::CONFIG_NAME);
    $allowed_roles = ['blog_editor'];

    $form['authors'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Auteurs'),
      '#description' => $this->t('Sélectionnez les auteurs à afficher dans le bloc "Ils publient".'),
      '#required' => TRUE,
    ];

    for ($i = 1; $i <= self::AUTHORS_NUMBER; $i++) {

      $form['authors']['author_' . $i] = [
        '#type' => 'entity_autocomplete',
        '#target_type' => 'user',
        '#selection_handler' => 'default:user',
        '#selection_settings' => [
          'include_anonymous' => FALSE,
          'filter' => [
            'type' => 'role',
            'role' => $allowed_roles,
          ],
        ],
        '#required' => TRUE,
        '#default_value' => (!empty($config->get('author_' . $i))) ? $this->entityTypeManager->getStorage('user')->load($config->get('author_' . $i)) : '',
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = $this->config(self::CONFIG_NAME);
    for ($i = 1; $i <= self::AUTHORS_NUMBER; $i++) {
      $config->set('author_' . $i, $form_state->getValue('author_' . $i));
    }
    $config->save();
    parent::submitForm($form, $form_state);
  }
}

<?php

namespace Drupal\icdc_investors\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'InvestorsModalBlock' block.
 *
 * @Block(
 *  id = "investors_modal_block",
 *  admin_label = @Translation("Investors modal block"),
 *  category = @Translation("ICDC")
 * )
 */
class InvestorsModalBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The Plugin Block Manager.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $languageManager)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager')
    );
  }

  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $build = parent::buildConfigurationForm($form, $form_state);

    // Use to build form (3 fields for each language).
    $languages = $this->languageManager->getLanguages();

    // State is used to saved configuration.
    $state = \Drupal::state();
    $params = $state->get('icdc_investors_modal');

    $build['input_page'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#selection_settings' => array(
        'target_bundles' => array('article','page','accueil'),
      ),
      '#title' => $this->t('Page d’entrée de l’espace investisseur'),
      '#description' => $this->t('Renseignez la page de rubrique investisseurs.'),
      '#default_value' => !is_null($params['fr']['input_page']) ? \Drupal::entityTypeManager()->getStorage('node')->load($params['fr']['input_page']) : '',
    ];

    foreach ($languages as $key => $language) {
      $params_lang = $params[$key];

      $build[$key . '_investors'] = [
        '#markup' => '<h2>' . $language->getName() . '</h2>'
      ];

      $build[$key . '_description'] = [
        '#type' => 'text_format',
        '#title' => $this->t('Texte d’information'),
        '#description' => $this->t('Saisissez le texte d’information présent dans la modale.'),
        '#default_value' => $params_lang['description'],
      ];
      $build[$key . '_btn_resident_label'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Libellé du bouton résident'),
        '#maxlength' => 255,
        '#size' => 64,
        '#default_value' => $params_lang['btn_resident_label'],
      ];
      $build[$key . '_btn_non_resident_label'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Libellé du bouton non résident'),
        '#maxlength' => 255,
        '#size' => 64,
        '#default_value' => $params_lang['btn_non_resident_label'],
      ];
    }

    return $build;
  }

  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $languages = $this->languageManager->getLanguages();

    // State is used to saved configuration.
    $state = \Drupal::state();
    $params = $state->get('icdc_investors_modal');

    $mutualized_fields = [
      'input_page'
    ];

    $individual_fields = [
      'description',
      'btn_resident_label',
      'btn_non_resident_label',
    ];

    $values = $form_state->getValues();

    foreach ($languages as $key => $language) {

      foreach ($mutualized_fields as $field) {
        $params[$key][$field] = $values[$field];
      }

      foreach ($individual_fields as $field) {
        $params[$key][$field] = is_string($values[$key . '_' . $field]) ? $values[$key . '_' . $field] : $values[$key . '_' . $field]['value'];
      }
    }

    $state->set('icdc_investors_modal', $params);

    parent::validateConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $config = \Drupal::state()->get('icdc_investors_modal')[$langcode];

    $build = [];
    $build['#theme'] = 'investors_modal_block';
    $build['#description'] = $config['description'];
    $build['#btn_resident_label'] = $config['btn_resident_label'];
    $build['#btn_non_resident_label'] = $config['btn_non_resident_label'];
    $build['#attached'] = [
      'library' => [
        'icdc_investors/investors_modal',
      ],
    ];
    return $build;
  }

  protected function blockAccess(AccountInterface $account) {
    // Only display modal on contributed page (/admin/cdc/modale-investisseurs).
    $node = \Drupal::routeMatch()->getParameter('node');

    if (!is_null($node)) {
      $state = \Drupal::state();
      $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();

      $node_investors_id = $state->get('icdc_investors_modal')[$langcode]['input_page'];

      if ($node_investors_id === $node->nid->value) {
        return AccessResult::allowed();
      }
    }

    return AccessResult::forbidden();
  }
}

<?php

namespace Drupal\daterange_formatter\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'custom_daterange_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "custom_daterange_formatter",
 *   label = @Translation("Affichage avec règles de gestion"),
 *   field_types = {
 *     "daterange"
 *   }
 * )
 */
class CustomDaterangeFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The custom date format.
   *
   * @var string
   */
  protected $dateCustomDate = 'date_full_simple';

  /**
   * Constructs a TimestampAgoFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, DateFormatterInterface $date_formatter) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    // @see \Drupal\Core\Field\FormatterPluginManager::createInstance().
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = ['#markup' => $this->viewValue($item)];
    }

    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return string
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    // The text value has no text format assigned to it, so the user input
    // should equal the output, including newlines.
    $string = '';
    $language = $item->getLangcode();
    $start_datetime = new \Datetime($item->value);
    $end_datetime = new \Datetime($item->end_value);

    // Manage dates.
    $start_year = $start_datetime->format('Y');
    $start_month = $start_datetime->format('m');
    $start_day = $start_datetime->format('d');

    $end_year = $end_datetime->format('Y');
    $end_month = $end_datetime->format('m');
    $end_day = $end_datetime->format('d');

    if ($start_year !== $end_year) {
      // RG2 = RG3.
      $string = $this->getFormattedDate($start_datetime->getTimestamp(), $end_datetime->getTimestamp(), $language);
    }
    elseif (($start_month !== $end_month) || (($start_month === $end_month && $start_day !== $end_day))) {
      // RG1 = RG3.
      $string = $this->getFormattedDate($start_datetime->getTimestamp(), $end_datetime->getTimestamp(), $language);
    }
    else {
      // RG4.
      $string = $this->dateFormatter->format($start_datetime->getTimestamp(), 'date_full_simple', '', NULL, $language);
    }
    return nl2br(Html::escape($string));
  }

  /**
   * Converts dates from Unix timestamps into custom date format.
   *
   * @param int $from
   *   An integer containing the Unix timestamp being converted.
   * @param int $to
   *   An integer containing the Unix timestamp being converted.
   * @param string $language
   *   The language to use.
   *
   * @return string
   *   The formatted range date.
   */
  protected function getFormattedDate($from, $to, $language) {
    $start_format = $this->dateFormatter->format($from, 'date_full_simple', '', NULL, $language);
    $end_format = $this->dateFormatter->format($to, 'date_full_simple', '', NULL, $language);
    $string = $this->t('From @start_date to @end_date', ['@start_date' => $start_format, '@end_date' => $end_format], ['langcode' => $language]);
    return $string;
  }

}

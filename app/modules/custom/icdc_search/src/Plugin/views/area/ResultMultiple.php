<?php

namespace Drupal\icdc_search\Plugin\views\area;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\area\Result;
use Drupal\views\Plugin\views\style\DefaultSummary;

/**
 * Views area handler to display some configurable result summary.
 *
 * @ingroup views_area_handlers
 *
 * @ViewsArea("result_multiple")
 */
class ResultMultiple extends Result {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['content_multiple'] = [
      'default' => $this->t('Displaying @start - @end of @total when @total is greater than 1'),
    ];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $form['content_multiple'] = [
      '#title' => $this->t('Display (multiple)'),
      '#type' => 'textarea',
      '#rows' => 3,
      '#default_value' => $this->options['content_multiple'],
      '#description' => $form['content']['#description'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE) {
    // Must have options and does not work on summaries.
    if (!isset($this->options['content']) || $this->view->style_plugin instanceof DefaultSummary) {
      return [];
    }
    $output = '';
    // Calculate the page totals.
    $current_page = (int) $this->view->getCurrentPage() + 1;
    $per_page = (int) $this->view->getItemsPerPage();
    // @TODO: Maybe use a possible is views empty functionality.
    // Not every view has total_rows set, use view->result instead.
    $total = isset($this->view->total_rows) ? $this->view->total_rows : count($this->view->result);
    $label = Html::escape($this->view->storage->label());
    // If there is no result the "start" and "current_record_count" should be
    // equal to 0. To have the same calculation logic, we use a "start offset"
    // to handle all the cases.
    $start_offset = empty($total) ? 0 : 1;
    if ($per_page === 0) {
      $page_count = 1;
      $start = $start_offset;
      $end = $total;
    }
    else {
      $page_count = (int) ceil($total / $per_page);
      $total_count = $current_page * $per_page;
      if ($total_count > $total) {
        $total_count = $total;
      }
      $start = ($current_page - 1) * $per_page + $start_offset;
      $end = $total_count;
    }
    $current_record_count = ($end - $start) + $start_offset;
    // Get the search information.
    $replacements = [];
    $replacements['@start'] = $start;
    $replacements['@end'] = $end;
    $replacements['@total'] = $total;
    $replacements['@label'] = $label;
    $replacements['@per_page'] = $per_page;
    $replacements['@current_page'] = $current_page;
    $replacements['@current_record_count'] = $current_record_count;
    $replacements['@page_count'] = $page_count;
    // Send the output.
    if (!empty($total) || !empty($this->options['empty'])) {
      $format = empty($total) || $total <2 ? $this->options['content'] : $this->options['content_multiple'];
      $output .= Xss::filterAdmin(str_replace(array_keys($replacements), array_values($replacements), $format));
      // Return as render array.
      return [
        '#markup' => $output,
      ];
    }

    return [];
  }

}

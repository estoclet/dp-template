<?php

namespace Drupal\mini_sites\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Views;

/**
 * Plugin implementation of the 'mini_site_view_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "mini_site_view_formatter",
 *   label = @Translation("View"),
 *   description = @Translation("Display a view."),
 *   field_types = {
 *     "mini_site_view"
 *   }
 * )
 */
class MiniSiteViewFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    /**
     * @var \Drupal\mini_sites\Entity\MiniSitePage
     */
    $miniSitePage = $items->getParent()->getValue();
    foreach ($items as $delta => $item) {
      $itemVal = $item->getValue();
      $view = Views::getView($itemVal['view_id']);
      if ($view) {
        $args = [$miniSitePage->get('mini_site')->target_id, $itemVal['view_target_bundle']];
        $view->setArguments($args);
        $view->setDisplay($itemVal['view_display']);
        $view->preExecute();
        $view->execute();
        $elements[$delta] = $view->buildRenderable($itemVal['view_display'], $args, FALSE);
        $elements[$delta]['#embed'] = TRUE;
      }
    }

    return $elements;
  }
}

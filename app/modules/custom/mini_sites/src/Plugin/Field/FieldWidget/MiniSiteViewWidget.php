<?php

namespace Drupal\mini_sites\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Entity\View;
use Drupal\views\Views;

/**
 * Plugin implementation of the 'mini_site_view_widget' widget.
 *
 * @FieldWidget(
 *   id = "mini_site_view_widget",
 *   label = @Translation("View"),
 *   description = @Translation("A field mini_site view."),
 *   field_types = {
 *     "mini_site_view"
 *   }
 * )
 */
class MiniSiteViewWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['view_id'] = [
      '#type' => 'value',
      '#value' => $items->getSetting('view_id')
    ];
    $element['view_display'] = [
      '#type' => 'value',
      '#value' => $items->getSetting('view_display')
    ];
    $element['view_target_bundle'] = [
      '#type' => 'value',
      '#value' => $items->getSetting('view_target_bundle')
    ];
    return $element;
  }

}

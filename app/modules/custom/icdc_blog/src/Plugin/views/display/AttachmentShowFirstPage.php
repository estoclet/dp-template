<?php

namespace Drupal\icdc_blog\Plugin\views\display;

use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\display\Attachment;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The plugin that handles an attachment display.
 *
 * Attachment displays are secondary displays that are 'attached' to a primary
 * display. Effectively they are a simple way to get multiple views within
 * the same view. They can share some information.
 *
 * @ingroup views_display_plugins
 *
 * @ViewsDisplay(
 *   id = "attachment_first_page",
 *   title = @Translation("Attachment displayed only for the first page"),
 *   help = @Translation("Attachments displayed only for the first page of result added to other displays to achieve multiple views in the same view."),
 *   theme = "views_view",
 *   contextual_links_locations = {""}
 * )
 */
class AttachmentShowFirstPage extends Attachment {

  /**
   * The request object.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a new Attachement instance.
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
  public function attachTo(ViewExecutable $view, $display_id, array &$build) {
    $displays = $this->getOption('displays');

    if (empty($displays[$display_id])) {
      return;
    }

    if (!$this->access()) {
      return;
    }

    if ($page = $this->requestStack->getCurrentRequest()->query->get('page')) {
      return;
    }

    $args = $this->getOption('inherit_arguments') ? $this->view->args : [];
    $view->setArguments($args);
    $view->setDisplay($this->display['id']);
    if ($this->getOption('inherit_pager')) {
      $view->display_handler->usesPager = $this->view->displayHandlers->get($display_id)->usesPager();
      $view->display_handler->setOption('pager', $this->view->displayHandlers->get($display_id)->getOption('pager'));
    }

    $attachment = $view->buildRenderable($this->display['id'], $args);

    switch ($this->getOption('attachment_position')) {
      case 'before':
        $this->view->attachment_before[] = $attachment;
        break;
      case 'after':
        $this->view->attachment_after[] = $attachment;
        break;
      case 'both':
        $this->view->attachment_before[] = $attachment;
        $this->view->attachment_after[] = $attachment;
        break;
    }

  }

}

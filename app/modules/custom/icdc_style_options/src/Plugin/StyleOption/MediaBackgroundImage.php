<?php

declare(strict_types=1);

namespace Drupal\icdc_style_options\Plugin\StyleOption;

use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\Core\Render\Renderer;
use Drupal\Component\Utility\Bytes;
use Drupal\Core\Ajax\AjaxHelperTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Environment;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\style_options\StyleOptionStyleTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\style_options\Plugin\StyleOptionPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Define the image attribute option plugin.
 *
 * @StyleOption(
 *   id = "media_background_image",
 *   label = @Translation("Media Image Attribute")
 * )
 */
class MediaBackgroundImage extends StyleOptionPluginBase {

  use AjaxHelperTrait;
  use StyleOptionStyleTrait;

  /**
   * The file url generator service.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    array $plugin_definition,
    Renderer $renderer,
    EntityTypeManagerInterface $entity_type_manager,
    FileUrlGeneratorInterface $file_url_generator) {

    parent::__construct($configuration, $plugin_id, $plugin_definition, $renderer, $entity_type_manager);
    $this->renderer = $renderer;
    $this->entityTypeManager = $entity_type_manager;
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition) {
    return new static(
        $configuration,
        $plugin_id,
        $plugin_definition,
        $container->get('renderer'),
        $container->get('entity_type.manager'),
        $container->get('file_url_generator')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(
    array $form,
    FormStateInterface $form_state): array {

    $scheme = $this->getConfiguration()['scheme'] ?? 'public';
    $directory = $this->getConfiguration()['directory'] ?? '';
    $env_max_upload_size = Environment::getUploadMaxSize();
    $max_size = $this->getConfiguration()['max_size'] ?? $env_max_upload_size;
    $max_filesize = min(Bytes::toNumber($max_size), $env_max_upload_size);
    $max_dimensions = $this->getConfiguration()['max_dimmensions'] ?? 0;

    $form['mid'] = [
      '#type' => 'media_library',
      '#allowed_bundles' => ['image'],
      '#title' => t('Upload your image'),
      '#default_value' => $this->getValue('mid') ?? $this->getDefaultValue(),
      '#description' => t('Upload or select your profile image.'),
    ];
    return $form;

  }

  /**
   * {@inheritDoc}
   */
  public function submitConfigurationForm(
    array &$form,
    FormStateInterface $form_state
  ): void {
    $values = $form_state->cleanValues()->getValues();
    if($values['mid'] && $media = Media::load($values['mid'])) {
      $values['mid'] = $this->entityTypeManager->getViewBuilder('media')->view($media, 'default');
    }
  }

  /**
   * {@inheritDoc}
   */
  public function build(array $build, $value = '') {
    $mid = $this->getValue('mid');
    $media = Media::load($mid);
    $fid = $media->field_media_image->target_id;
    if (!empty($fid) && $file_object = File::load($fid)) {

      $file_uri = $file_object->getFileUri();
      $file_url = $this->fileUrlGenerator->generate($file_uri)->toString();

      if ($this->getConfiguration('method') == 'css') {
        $build['#attributes']['style'][] = 'background-image: url(' . $file_url . ');';
      }
    }
    return $build;
  }

}

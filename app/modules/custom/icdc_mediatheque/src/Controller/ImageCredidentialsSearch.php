<?php

namespace Drupal\icdc_mediatheque\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\media\MediaStorage;


/**
 * Class ImageCredidentialsSearch.
 *
 */
class ImageCredidentialsSearch extends ControllerBase {


  protected $mediaStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->mediaStorage = $entity_type_manager->getStorage('media');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Handler for autocomplete request.
   */
  public function handleAutocomplete(Request $request) {
    $results = [];
   
    // Get the typed string from the URL, if it exists.
    $input = $request->query->get('q');

    // Get By Title
    $input = Xss::filter($input);
    $query_by_name = $this->mediaStorage->getQuery();
    $query_by_name->condition('name', $input, 'CONTAINS');
    $query_by_name->groupBy('name');
    $query_by_name->range(0, 10);
    $ids_media = $query_by_name->execute();
    $medias = !empty($ids_media) ? $this->mediaStorage->loadMultiple($ids_media) : [];

    // Set Search Results Autocomplete
    $results = [];
    foreach($medias as $media) {
      $results[] = $media->getName();
    }

    // Send Results
    return new JsonResponse($results);
  }
}
<?php

namespace Drupal\icdc_didomi_embed\TwigExtension;

use Drupal\icdc_didomi_embed\Services\EmbedProviderService;

/**
 * Class Embed Twig Extension.
 */
class EmbedTwigExtension extends \Twig_Extension {

  /**
   * EmbedProviderService variable.
   *
   * @var \Drupal\icdc_didomi_embed\EmbedProviderService
   */
  private $embedProviderService;

  /**
   * Construct function.
   *
   * @param \Drupal\icdc_didomi_embed\Services\EmbedProviderService $embed_provider_service
   *   The Embed provider service.
   */
  public function __construct(EmbedProviderService $embed_provider_service) {
    $this->embedProviderService = $embed_provider_service;
  }

  /**
   * Generates a list of all Twig filters that this extension defines.
   */
  public function getFilters() {
    return [
      new \Twig_SimpleFilter('iframe_constent', [$this, 'iframeConstent']),
    ];
  }

  /**
   * IframeConstent function.
   *
   * @param string $iframe_code
   *   The iframe code.
   * @param null|array $options
   *   Array of options.
   *
   * @return array
   *   The render array.
   */
  public function iframeConstent(string $iframe_code, ?array $options = []):array {
    $content['iframe_code'] = $iframe_code;
    $content['parent_id'] = 'element_' . uniqid();
    $build = [
      '#theme' => 'icdc_didomi_embed',
      '#attached' => [
        'library' => [
          'icdc_didomi_video_embed/icdc_didomi_video_embed',
        ],
      ],
    ];
    if (preg_match('/src="([^"]+)"/', $iframe_code, $match)) {
      $url = $match[1];
      $provider_id = $this->embedProviderService->getProviderIdByUrl($url);
      if (!empty($provider_id) && $this->embedProviderService->hasRequireConsent($provider_id)) {
        $provider_data = $this->embedProviderService->getProviderData($provider_id);
        $content['provider'] = $provider_data;
        $build['#attached']['drupalSettings'] = [
          'icdc_didomi_embed' => [
            'providers' => [
              $provider_id => [
                'vendor_id' => $provider_data['vendor']['id'],
                'options' => $options,
                'parent_id' => $content['parent_id'],
              ],
            ],
          ],
        ];
      }
    }
    $build['#content'] = $content;
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'icdc_didomi_embed.twig.extension';
  }

}

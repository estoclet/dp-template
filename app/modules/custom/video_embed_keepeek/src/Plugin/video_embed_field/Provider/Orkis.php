<?php


namespace Drupal\video_embed_keepeek\Plugin\video_embed_field\Provider;


use Drupal\video_embed_field\ProviderPluginBase;

/**
 * @VideoEmbedProvider(
 *   id = "orkis",
 *   title = @Translation("Orkis")
 * )
 */
class Orkis extends ProviderPluginBase
{

  /**
   * Get the URL of the remote thumbnail.
   *
   * This is used to download the remote thumbnail and place it on the local
   * file system so that it can be rendered with image styles. This is only
   * called if no existing file is found for the thumbnail and should not be
   * called unnecessarily, as it might query APIs for video thumbnail
   * information.
   *
   * @return string
   *   The URL to the remote thumbnail file.
   */
  public function getRemoteThumbnailUrl()
  {
    // TODO: Use Keepeek API to get automatic thumbnail.
    return file_create_url('https://picteo.caissedesdepots.fr/caissedesdepots/images/logo-white.png');
  }

  /**
   * Render embed code.
   *
   * @param string $width
   *   The width of the video player.
   * @param string $height
   *   The height of the video player.
   * @param bool $autoplay
   *   If the video should autoplay.
   *
   * @return mixed
   *   A renderable array of the embed code.
   */
  public function renderEmbedCode($width, $height, $autoplay)
  {
    $self_properties = get_object_vars($this);
    $self_properties_tab = gettype($self_properties) == 'array' ? array_keys($self_properties) : [];
    $id = in_array('videoId', $self_properties_tab) !== false ? $this->videoId : '';

    $iframe = [
      '#type' => 'video_embed_iframe',
      '#provider' => 'keepeek',
      '#url' => 'https://picteo.caissedesdepots.fr/cdc/embed/'. $id,
      '#attributes' => [
        'border' => 0,
        'width' => $width,
        'height' => $height,
        'frameborder' => '0',
        'allowfullscreen' => 'allowfullscreen',
      ],
    ];

    return $iframe;
  }

  /**
   * Get the ID of the video from user input.
   *
   * @param string $input
   *   Input a user would enter into a video field.
   *
   * @return string
   *   The ID in whatever format makes sense for the provider.
   */
  public static function getIdFromInput($input = false)
  {
    $matches['id'] = false;
    if($input != false && gettype($input) == 'string') {
      if(strpos($input, 'https://picteo.caissedesdepots.fr/cdc/embed/') !== false) {
        $id_string = str_replace('https://picteo.caissedesdepots.fr/cdc/embed/', '', $input);
        $matches['id'] = intval($id_string);
      }
    }
    return $matches['id'];
  }
}

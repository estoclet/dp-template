<?php

namespace Drupal\icdc_mediatheque\Services;

use Drupal\Component\Utility\UrlHelper;
use Drupal\file\Entity\File;
use Drupal\Core\StreamWrapper\PublicStream;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\RequestException;

class AushaAPIService {

    public function __construct() {
    }

    public function getPodcastDatas ($podcast_id = '') {
        // Get Ausha API connexion informations
        $ausha_config = \Drupal::config('icdc_mediatheque.ausha_api_connector')->getRawData();
        $base_url = $ausha_config['url'];
        $base_token = $ausha_config['token'];
        // Set request url
        $url = $base_url."podcasts/public_id/".$podcast_id;
        // Set request auth token
        $token = 'Bearer '.$base_token;

        // Build request and get Ausha Datas
        $data_tab = [];
        try {
          $client = new Client([
            'headers' => [
              'accept' => 'application/json',
              'content-type' => 'application/json',
              'Authorization' => $token
            ]
          ]);
          $response = $client->get($url);
          if($response->getStatusCode() == 200) {
            $data = $response->getBody()->getContents();
            if(!empty($data)) {
              $content = json_decode($data);
              if(!empty($content)) {
                $data_tab = $content->data;
              }
            }
          }
          return $data_tab;
        }
        catch (\Exception $e) {
          \Drupal::logger('icdc_mediatheque')->notice($e);
          return [];
        }
    }

    public function createThumbnails ($url = '', $slug = '') {
        if(empty($url)) {
            return [];
        }
        if(empty($slug)) {
            return [];
        }
        // Create thumbnail file in system
        $base_path = PublicStream::basePath(\Drupal::service('site.path'));
        $ausha_folder = $base_path.'/ausha_thumbnails/';
        // Create directory if not exist
        if (!is_dir($ausha_folder)) {
            mkdir($ausha_folder);
        }
        // Create file if not exist
        if(file_exists($ausha_folder.$slug.'.jpeg') !== TRUE) {
            file_put_contents($ausha_folder.$slug.'.jpeg', fopen($url, 'r'));
        }
        // // Create file entity.
        $query = \Drupal::entityTypeManager()->getStorage('file')->getQuery();
        $query->condition('filename', $slug.'.jpeg');
        $file_id = $query->execute();
        // If file entity exists
        $thumbnail_entity = [];

        if(!empty($file_id)) {
            $thumbnail_entity = File::load(reset($file_id));
        // If file entity not exist
        } else {
            $destination = 'public://ausha_thumbnails/';
            $thumbnail_entity = File::create();
            $thumbnail_entity->setFileUri($destination.$slug.'.jpeg');
            $thumbnail_entity->setOwnerId(\Drupal::currentUser()->id());
            $thumbnail_entity->setMimeType('image/jpeg');
            $thumbnail_entity->setFileName($slug.'.jpeg');
            $thumbnail_entity->setPermanent();
            $thumbnail_entity->save();
        }
        // Return Thumbnails infos
        if(!empty($thumbnail_entity)) {
            $thumbnail_datas = [
                'target_id' => intval($thumbnail_entity->id()),
                'alt' => $slug,
            ];
            return $thumbnail_datas;
        } else {
            return [];
        }
    }
}
<?php

namespace Drupal\icdc_didomi;

use Drupal\Core\Render\Markup;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Config\ConfigFactory;

/**
 * Class Container Manager.
 */
class ContainerManager {

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Didomi Config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Constructs a new ContainerManager object.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory, ConfigFactory $configFactory) {
    $this->logger = $logger_factory->get('icdc_didomi');
    $this->config = $configFactory->get('icdc_didomi.settings');
  }

  /**
   * Returns JavaScript script snippet.
   *
   * @return array
   *   The script snippet.
   */
  protected function scriptSnippet() {
    $public_api_key = $this->getPublicApiKey();
    // Build script snippet.
    $script = '
    window.gdprAppliesGlobally=true;(function(){function a(e){if(!window.frames[e]){if(document.body&&document.body.firstChild){var t=document.body;var n=document.createElement("iframe");n.style.display="none";n.name=e;n.title=e;t.insertBefore(n,t.firstChild)}
    else{setTimeout(function(){a(e)},5)}}}function e(n,r,o,c,s){function e(e,t,n,a){if(typeof n!=="function"){return}if(!window[r]){window[r]=[]}var i=false;if(s){i=s(e,t,n)}if(!i){window[r].push({command:e,parameter:t,callback:n,version:a})}}e.stub=true;function t(a){if(!window[n]||window[n].stub!==true){return}if(!a.data){return}
    var i=typeof a.data==="string";var e;try{e=i?JSON.parse(a.data):a.data}catch(t){return}if(e[o]){var r=e[o];window[n](r.command,r.parameter,function(e,t){var n={};n[c]={returnValue:e,success:t,callId:r.callId};a.source.postMessage(i?JSON.stringify(n):n,"*")},r.version)}}
    if(typeof window[n]!=="function"){window[n]=e;if(window.addEventListener){window.addEventListener("message",t,false)}else{window.attachEvent("onmessage",t)}}}e("__tcfapi","__tcfapiBuffer","__tcfapiCall","__tcfapiReturn");a("__tcfapiLocator");(function(e){
    var t=document.createElement("script");t.id="spcloader";t.type="text/javascript";t.async=true;t.src="https://sdk.privacy-center.org/"+e+"/loader.js?target="+document.location.hostname;t.charset="utf-8";var n=document.getElementsByTagName("script")[0];n.parentNode.insertBefore(t,n)})("{{public_api_key}}")})();
    ';
    $script = str_replace("{{public_api_key}}", $public_api_key, $script);
    $script = str_replace(["\n", '  '], '', $script);
    return $script;
  }

  /**
   * Get Public Api Key.
   *
   * @return string
   *   The public api key.
   */
  public function getPublicApiKey():string {
    return $this->config->get('public_api_key');
  }

  /**
   * Is published.
   *
   * @return bool
   *   True => Published, FALSE => not published
   */
  public function isPublished():bool {
    if ($this->config->get('publish') === 1) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Get Paths List Condition function.
   *
   * @return string
   *   The paths list toogle condition.
   */
  public function getPathsListCondition():string {
    $condition = $this->config->get('paths_list_condition');
    if ($condition === NULL) {
      return "DIDOMI_CONSENT_EXCLUDE_LISTED";
    }
    return $condition;
  }

  /**
   * Get Paths list.
   *
   * @return string
   *   The paths list patterns
   */
  public function getPathsList():string {
    return $this->config->get('paths_list');
  }

  /**
   * Determines whether to insert the snippet based on the path settings.
   *
   * @return bool
   *   TRUE if the path conditions are met; FALSE otherwise.
   */
  public function pathCheck():bool {
    $paths = mb_strtolower($this->getPathsList());
    $condition = $this->getPathsListCondition();
    $request = \Drupal::request();
    $current_path = \Drupal::service('path.current');
    $alias_manager = \Drupal::service('path_alias.manager');
    $path_matcher = \Drupal::service('path.matcher');
    $path = $current_path->getPath($request);
    $path_alias = mb_strtolower($alias_manager->getAliasByPath($path));
    $satisfied = $path_matcher->matchPath($path_alias, $paths) || (($path != $path_alias) && $path_matcher->matchPath($path, $paths));
    if ($condition === "DIDOMI_CONSENT_EXCLUDE_LISTED" && $satisfied === FALSE) {
      return TRUE;
    }
    elseif ($condition === "DIDOMI_CONSENT_INCLUDE_LISTED" && $satisfied === TRUE) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getScriptAttachments(array &$attachments) {
    $script = $this->scriptSnippet();
    if (!empty($script)) {
      $attachment = [
      [
        '#type' => 'html_tag',
        '#tag' => 'script',
        '#value' => Markup::create($script),
        '#weight' => 0,
        '#attributes' => [
          'type' => 'text/javascript',
        ],
      ], 'icdc-didomi-embed-code',
      ];
      $provider_service = \Drupal::service('icdc_didomi_embed.provider');
      $providers = $provider_service->getProviders();
      if (!empty($attachments['#attached']['html_head']) && is_array($attachments['#attached']['html_head'])) {
        array_unshift($attachments['#attached']['html_head'], $attachment);
      } else {
        $attachments['#attached']['html_head'][] = $attachment;
      }
      $attachments['#attached']['library'][] = 'icdc_didomi/icdc_didomi';
      $attachments['#attached']['drupalSettings']['icdcDidomiProviders'] = $providers;
    }
  }

}

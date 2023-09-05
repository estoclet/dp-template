<?php

namespace Drupal\icdc_social_simple;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\social_simple\SocialSimpleGenerator;

class IcdcSocialSimpleGenerator extends SocialSimpleGenerator {

  /**
   * {@inheritdoc}
   */
  public function getShareUrl(EntityInterface $entity = NULL) {

    $language = \Drupal::languageManager()->getCurrentLanguage(\Drupal\Core\Language\LanguageInterface::TYPE_CONTENT)->getId();

    if ($entity) {
      $share_url = $entity->toUrl('canonical', ['absolute' => FALSE,  'language' => \Drupal::languageManager()->getLanguage($language)])->toString();
    }
    else {
      $share_url = Url::fromRoute('<current>', [], ['absolute' => 'false',  'language' => \Drupal::languageManager()->getLanguage($language)])->toString();
    }

    return '___BASE_URL___' . $share_url;
  }
}

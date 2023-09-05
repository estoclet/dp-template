<?php

namespace Drupal\mini_sites\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\mini_sites\Entity\MiniSite;
use Drupal\mini_sites\Entity\MiniSiteType;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Mini site page edit forms.
 *
 * @ingroup mini_sites
 */
class MiniSitePageForm extends ContentEntityForm {

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * Constructs a new MiniSitePageForm.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The current user account.
   */
  public function __construct(EntityRepositoryInterface $entity_repository, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL, AccountProxyInterface $account) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);

    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('current_user')
    );
  }

  protected function init(FormStateInterface $form_state) {
    parent::init($form_state);
    if($this->entity->isNew()) {
      $miniSiteId = $this->getRequest()->get('mini_site');
      $miniSiteEntity = MiniSite::load($miniSiteId);
      $miniSiteType = MiniSiteType::load($miniSiteEntity->bundle());
      $miniSitePageType = $this->getRequest()->get('mini_site_page_type')->id();
      if(empty(array_filter($miniSiteType->get('allow_page_type')))) {
        $allowType = $this->entityTypeBundleInfo->getBundleInfo('mini_site_page');
      }
      else {
        $allowType = $miniSiteType->get('allow_page_type');
      }

      if($miniSiteEntity->menu_type->value == 'anchor') {
        $allowType = !empty($allowType['link']) ? ['link' => $allowType['link']] : [];
      }

      \Drupal::moduleHandler()->alter('mini_site_page_allowed_type', $allowType, $miniSiteEntity);

      if(empty($allowType[$miniSitePageType])) {
        $response = $this->redirect('entity.mini_site_page.add_page', ['mini_site' => $miniSiteId]);
        $response->send();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var \Drupal\mini_sites\Entity\MiniSitePage $entity */
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Mini site page.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Mini site page.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.mini_site_page.collection', ['mini_site' => $entity->get('mini_site')->target_id]);
  }

}

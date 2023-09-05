<?php

namespace Drupal\icdc_node_weight_order\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\ConfirmFormHelper;
use Drupal\Core\Form\ConfirmFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\icdc_node_weight_order\IcdcNodeWeightManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides node overview form for a icdc node order module.
 *
 * @internal
 */
class IcdcDeleteKeywords extends ConfirmFormBase implements ConfirmFormInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\icdc_node_weight_order\IcdcNodeWeightManager
   */
  protected $manager;

  /**
   * Keyword object
   * @var StdClass
   */
  protected $keywords;

  /**
   * Class constructor.
   */
  public function __construct(IcdcNodeWeightManager $manager) {
    $this->manager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
    // Load the service required to construct this class.
      $container->get('icdc_node_weight_order.node_weight_manager')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @param int $idKeywords
   *   The keywords id.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $idKeyword = NULL) {
    if (!$this->keywords = $this->manager->getKeyword($idKeyword)) {
      throw new NotFoundHttpException();
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * @inheritDoc
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete keyword "%keywords" ?', ['%keywords' => $this->keywords->keywords]);
  }

  /**
   * @inheritDoc
   */
  public function getCancelUrl() {
    return new Url('icdc_node_weight_order.admin_order');
  }

  /**
   * @inheritDoc
   */
  public function getFormId() {
    return 'icdc_node_order_delete';
  }

  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->manager->deleteKeyword($this->keywords->id);
    $form_state->setRedirectUrl($this->getCancelUrl());
  }
}

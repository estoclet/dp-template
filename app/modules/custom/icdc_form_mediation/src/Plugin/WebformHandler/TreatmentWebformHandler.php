<?php

namespace Drupal\icdc_form_mediation\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\Component\Utility\Html;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Component\Datetime\DateTimePlus;

/**
 * Add Treatment from a webform submission.
 *
 * @WebformHandler(
 *   id = "Add Treatment",
 *   label = @Translation("Add Treatment"),
 *   category = @Translation("Settings"),
 *   description = @Translation("Add Treatment from Webform Submissions."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */

class TreatmentWebformHandler extends WebformHandlerBase
{
  use StringTranslationTrait;
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission)
  {
    $this->validateDate($form, $form_state);
  }
  /**
   * Validate date of birth.
   */
  private function validateDate(array &$form, FormStateInterface $formState)
  {
    $mediationDateTime = "";
    $birthValue = Html::escape($formState->getValue('date_de_naissance'));
    $mediationValue = Html::escape($formState->getValue('mediation_entity_status_date'));
    $birthDate = !empty($formState->getValue('date_de_naissance')) ? date('Y-m-d', strtotime(str_replace('/', '-', Html::escape($formState->getValue('date_de_naissance'))))) : null;
    $mediationDate  = !empty($formState->getValue('mediation_entity_status_date')) ? date('Y-m-d', strtotime(str_replace('/', '-', Html::escape($formState->getValue('mediation_entity_status_date'))))) : null;
    $currentDate = date("Y-m-d", strtotime("-1 Days"));
    //Curent date
    $dateCurrent = new DateTimePlus($currentDate);
    $currentDateTime = $dateCurrent->getTimestamp();
    // Birth date
    $dateBirth = new DateTimePlus($birthDate);
    $birthDateTime = strtotime($birthDate);
    // Mediation date
    if(!empty($mediationDate)){
      $dateMediation = new DateTimePlus($mediationDate);
      $mediationDateTime = strtotime($mediationDate);
    }


    if (!empty($birthDate)) {
      if (!empty($errorMessageBirth = $this->checkValidateDate($birthValue, "date of birth"))) {
        $formState->setErrorByName('date_de_naissance', $errorMessageBirth);
      } elseif ($birthDateTime > $currentDateTime) {
        $formState->setErrorByName('date_de_naissance', $this->t("La date de naissance doit être inférieure à la date du jour."));
      } else {
        $formState->setValue('date_de_naissance', $birthValue);
      }
    }

    if (!empty($mediationDate)) {
      if (!empty($errorMessageMediation = $this->checkValidateDate($mediationValue, "date of mediation"))) {
        $formState->setErrorByName('mediation_entity_status_date', $errorMessageMediation);
      } elseif ($mediationDateTime > $currentDateTime) {
        $formState->setErrorByName('mediation_entity_status_date', $this->t("La date saisie doit être inférieure à la date du jour."));
      } else {
        $formState->setValue('mediation_entity_status_date', $mediationValue);
      }
    }

  }

  // Function to be fired after submitting the Webform.
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE)
  {
    // Get an array of the values from the submission.
    $values = $webform_submission->getData();
    $date_formatter = \Drupal::service('date.formatter');

    $Ref_Part1  =  \Drupal::time()->getCurrentTime();
    $Ref_Part1  = $date_formatter->format($Ref_Part1, 'custom', 'y');
    $Calc_Part2 = intval($webform_submission->serial() / 100);
    $Ref_Part2 = $this->getNameFromNumber($Calc_Part2);
    $Ref_Part3  = ($webform_submission->serial() % 100);
    $Reference = $Ref_Part1."_". $Ref_Part2 ."_".$Ref_Part3."_F";

    $data = $webform_submission->getData();
    $data['reference_client_id'] = $Reference;
    $webform_submission->setData($data);
    $webform_submission->resave();


  }

  public function getNameFromNumber($num)
  {
    $numeric = $num % 26;
    $letter = chr(65 + $numeric);
    $num2 = intval($num / 26);
    if ($num2 > 0) {
      return getNameFromNumber($num2 - 1) . $letter;
    } else {
      return $letter;
    }
  }

  public function checkValidateDate($date, $type = "date")
  {
    $d = \DateTime::createFromFormat('Y-m-d', $date);
    // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
    //  return $d && $d->format('d/m/Y') === $date;
    $error = "";
    $dateArr = explode("-", $date);
    if (!($d && $d->format('Y-m-d') === $date)) {
      $error = $this->t('The "' . $type . '" must be in the "DD/MM/YYYY" format' );
    } elseif (!checkdate($dateArr[1], $dateArr[2], $dateArr[0])) {
      $error = $this->t('The "' . $type . '"  is not valid.');
    }
    return $error;
  }


}

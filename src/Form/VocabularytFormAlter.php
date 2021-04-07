<?php


namespace Drupal\corporate\Form;


use Drupal\Core\Form\FormStateInterface;
use Drupal\form_alter_service\FormAlterBase;
use Drupal\user\Entity\User;

class VocabularytFormAlter extends FormAlterBase {

  /**
   * @inheritDoc
   */
  public function alterForm(array &$form, FormStateInterface $form_state): void {
    if (!\Drupal::currentUser()->hasPermission('administer vocabularies')) {
      $form['vid']['#access'] = FALSE;
      $form['hierarchy']['#access'] = FALSE;
      $form['menu']['#access'] = FALSE;
      $form['langcode']['#access'] = FALSE;
      $form['actions']['delete']['#access'] = FALSE;
      $form['default_terms_language']['#access'] = FALSE;
    }
  }

}

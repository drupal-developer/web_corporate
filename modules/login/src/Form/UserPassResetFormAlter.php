<?php


namespace Drupal\login\Form;


use Drupal\Core\Form\FormStateInterface;
use Drupal\form_alter_service\FormAlterBase;

class UserPassResetFormAlter extends FormAlterBase {

  /**
   * @inheritDoc
   */
  public function alterForm(array &$form, FormStateInterface $form_state): void {
    $form['actions']['submit']['#attributes']['class'] = ['button js-form-submit form-submit btn'];
  }

}

<?php


namespace Drupal\corporate\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\form_alter_service\FormAlterBase;

class NodeFormAlter extends FormAlterBase {

  public function alterForm(array &$form, FormStateInterface $form_state): void {
    $user = \Drupal::currentUser();
    if (!$user->hasPermission('access node revision')) {
      $form['revision_information']['#access'] = FALSE;
    }
  }
}

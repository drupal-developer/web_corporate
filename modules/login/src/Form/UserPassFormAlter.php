<?php


namespace Drupal\login\Form;


use Drupal\Core\Form\FormStateInterface;
use Drupal\form_alter_service\FormAlterBase;

class UserPassFormAlter extends FormAlterBase {

  /**
   * @inheritDoc
   */
  public function alterForm(array &$form, FormStateInterface $form_state): void {
    unset($form['name']['#description']);
    $form['name']['#title_display'] = 'invisible';
    $form['name']['#placeholder'] = t('Email');
    unset($form['mail']);
    $form['actions']['submit']['#value'] = t('Send password');
    $form['actions']['submit']['#attributes']['class'] = ['button js-form-submit form-submit btn'];
    $form['#validate'][0] = '::validateForm';
    $form['#submit'][0] = '::submitForm';
  }

}

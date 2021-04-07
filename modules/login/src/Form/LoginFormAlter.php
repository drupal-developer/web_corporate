<?php


namespace Drupal\login\Form;


use Drupal\Core\Form\FormStateInterface;
use Drupal\form_alter_service\FormAlterBase;

class LoginFormAlter extends FormAlterBase {

  /**
   * @inheritDoc
   */
  public function alterForm(array &$form, FormStateInterface $form_state): void {
    unset($form['name']['#description']);
    unset($form['pass']['#description']);
    $form['name']['#title_display'] = 'invisible';
    $form['name']['#placeholder'] = t('Email');

    $form['pass']['#title_display'] = 'invisible';
    $form['pass']['#placeholder'] = t('Password');

    $form['actions']['submit']['#attributes']['class'] = ['button js-form-submit form-submit btn'];
  }

}

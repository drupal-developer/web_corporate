<?php


namespace Drupal\login\Form;


use Drupal\Core\Form\FormStateInterface;
use Drupal\form_alter_service\FormAlterBase;

class UserRegisterFormAlter extends FormAlterBase {

  /**
   * @inheritDoc
   */
  public function alterForm(array &$form, FormStateInterface $form_state): void {

    unset($form['account']['mail']['#description']);
    unset($form['account']['pass']['#description']);
    $form['account']['mail']['#title_display'] = 'invisible';
    $form['account']['mail']['#placeholder'] = t('Email');
    $form['account']['mail']['#attributes']['autocomplete'] = 'off';
    $form['account']['mail']['#weight'] = 1;
    $form['account']['pass']['#weight'] = 3;
    $form['account']['confirm_mail'] = $form['account']['mail'];
    $form['account']['confirm_mail']['#placeholder'] = t('Confirm Email');
    $form['account']['confirm_mail']['#element_validate'] = ['_login_form_register_mail_validate'];
    $form['account']['confirm_mail']['#weight'] = 2;
    $form['actions']['submit']['#attributes']['class'] = ['button js-form-submit form-submit btn'];

    if (isset($form['field_nombre'])) {
      $form['field_nombre']['widget'][0]['value']['#title_display'] = 'invisible';
      $form['field_nombre']['widget'][0]['value']['#placeholder'] = $form['field_nombre']['widget'][0]['value']['#title'];
    }

    if (isset($form['field_apellidos'])) {
      $form['field_apellidos']['widget'][0]['value']['#title_display'] = 'invisible';
      $form['field_apellidos']['widget'][0]['value']['#placeholder'] = $form['field_apellidos']['widget'][0]['value']['#title'];
    }

  }

}

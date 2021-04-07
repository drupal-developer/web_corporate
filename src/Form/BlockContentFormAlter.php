<?php


namespace Drupal\corporate\Form;


use Drupal\Core\Form\FormStateInterface;
use Drupal\form_alter_service\Annotation\FormSubmit;
use Drupal\form_alter_service\FormAlterBase;

class BlockContentFormAlter extends FormAlterBase {

  /**
   * @inheritDoc
   */
  public function alterForm(array &$form, FormStateInterface $form_state): void {
    $user = \Drupal::currentUser();
    if (!$user->hasPermission('access node revision')) {
      $form['revision_information']['#access'] = FALSE;
      $form['info']['#access'] = FALSE;
    }
    $form['actions']['submit']['#submit'][] = [get_called_class(), '_redirect_form_block_content'];
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public static function _redirect_form_block_content(array $form, FormStateInterface $form_state) {
    $entity = $form_state->getFormObject()->getEntity();
    $form_state->setRedirect('entity.block_content.edit_form', ['block_content' => $entity->id()]);
  }

}

<?php


namespace Drupal\redes\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\redes\Entity\RedSocial;

class RedSocialForm extends ContentEntityForm {

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    /** @var \Drupal\redes\Entity\RedSocial $entity */
    $entity = $this->entity;
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * @inheritDoc
   */
  public function validateForm(array &$form, FormStateInterface $form_state): ContentEntityInterface {
    parent::validateForm($form, $form_state);
    /** @var \Drupal\redes\Entity\RedSocial $entity */
    $entity = $this->entity;
    return $entity;
  }

}

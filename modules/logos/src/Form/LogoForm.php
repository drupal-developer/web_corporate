<?php


namespace Drupal\logos\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\logos\Entity\Logo;

class LogoForm extends ContentEntityForm {

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    /** @var \Drupal\logos\Entity\Logo $entity */
    $entity = $this->entity;
    $form = parent::buildForm($form, $form_state);

    if (!$entity->isNew()) {
      $form['type']['widget']['#disabled'] = TRUE;
    }

    return $form;
  }

  /**
   * @inheritDoc
   */
  public function validateForm(array &$form, FormStateInterface $form_state): ContentEntityInterface {
    parent::validateForm($form, $form_state);
    /** @var \Drupal\logos\Entity\Logo $entity */
    $entity = $this->entity;
    $values = $form_state->getValues();
    $tipo = $values['type'][0]['value'];

    if ($entity->isNew()) {
      $tipo = $values['type'][0]['value'];
      $database = \Drupal::database();
      $sql = "SELECT id FROM logos where type = '" . $tipo . "'";
      $result = $database->query($sql);
      if ($result) {
        while ($row = $result->fetchAssoc()) {
          $form_state->setErrorByName('type', 'Ya existe un logo para el tipo ' . $tipo);
          break;
        }
      }
    }

    if ($tipo == Logo::TYPE_MAIL && isset($values['logo'][0]['fids'][0])) {
      $file_id = $values['logo'][0]['fids'][0];
      $file = File::load($file_id);
      if ($file instanceof File) {
        $mime = $file->getMimeType();
        if (preg_match("/svg*/i", $mime)) {
          $form_state->setErrorByName('logo', 'No se permiten archivos SVG para el logo del correo');
        }
      }
    }

    return $entity;
  }

}

<?php


namespace Drupal\redes\Entity;


use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\file\Entity\File;

/**
 * Entidad Red Social.
 *
 * @ingroup red_social
 *
 * @ContentEntityType(
 *   id = "red_social",
 *   label = @Translation("Red Social"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\redes\Access\RedSocialAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\redes\Routing\RedSocialHtmlRouteProvider",
 *     },
 *   "form" = {
 *       "edit" = "Drupal\redes\Form\RedSocialForm",
 *       "add" = "Drupal\redes\Form\RedSocialForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *   },
 *   base_table = "redes_sociales",
 *   admin_permission = "administer red_social entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "label" = "name"
 *   },
 *   links = {
 *     "delete-form" = "/admin/config/red_social/{red_social}/delete",
 *     "edit-form" = "/admin/config/red_social/{red_social}/edit",
 *     "add-form" = "/admin/config/red_social/add",
 *   }
 * )
 */
class RedSocial extends ContentEntityBase {

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel('Nombre')
      ->setDescription('')
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['url'] = BaseFieldDefinition::create('string')
      ->setLabel('Url')
      ->setDescription('')
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['icono'] = BaseFieldDefinition::create('file')
      ->setLabel('Icono')
      ->setRequired(TRUE)
      ->setSettings(
        [
          'file_directory' => 'redes_sociales',
          'alt_field_required' => FALSE,
          'file_extensions' => 'svg',
          'max_filesize' => '5M',
        ]
      )
      ->setDisplayOptions('form', [
        'type' => 'file',
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

}

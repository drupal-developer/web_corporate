<?php


namespace Drupal\email\Entity;


use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Entidad Mail.
 *
 * @ingroup mail
 *
 * @ContentEntityType(
 *   id = "mail",
 *   label = @Translation("Mail"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\email\Access\MailAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\email\Routing\MailHtmlRouteProvider",
 *     },
 *   "form" = {
 *       "edit" = "Drupal\email\Form\MailForm",
 *       "add" = "Drupal\email\Form\MailForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *   },
 *   base_table = "mails",
 *   translatable = TRUE,
 *   admin_permission = "administer mail entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode"
 *   },
 *   links = {
 *     "delete-form" = "/admin/config/mail/{mail}/delete",
 *     "edit-form" = "/admin/config/mail/{mail}/edit",
 *     "add-form" = "/admin/config/mail/add",
 *   },
 *   field_ui_base_route = "mail.settings"
 * )
 */
class Mail extends ContentEntityBase {

  const TYPE_REGISTER = 'register';
  const TYPE_RESET_PASSWORD = 'reset_password';
  const TYPE_CONFIRM_ORDER = 'order_completed';
  const TYPE_CONFIRM_ORDER_STORE = 'order_completed_store';
  const TYPE_SENT_ORDER = 'order_sent';
  const TYPE_STOCK_ALERT = 'stock_alert';
  const TYPE_STOCK_ALERT_COMPLETE = 'stock_alert_complete';

  public static function getTypes() {
    $types = [
      self::TYPE_REGISTER => 'Registro',
      self::TYPE_RESET_PASSWORD => 'Recuperar contraseña',
    ];

    $moduleHandler = \Drupal::service('module_handler');
    if ($moduleHandler->moduleExists('commerce')) {
      $types[self::TYPE_CONFIRM_ORDER] = 'Confirmar pedido cliente';
      $types[self::TYPE_CONFIRM_ORDER_STORE] = 'Confirmar pedido tienda';
      $types[self::TYPE_SENT_ORDER] = 'Pedido enviado';
    }

    if ($moduleHandler->moduleExists('stock')) {
      $types[self::TYPE_STOCK_ALERT] = 'Aviso de stock';
      $types[self::TYPE_STOCK_ALERT_COMPLETE] = 'Aviso de stock 15 dias';
    }


    return $types;
  }

  /**
   * @inheritDoc
   */
  public static function load($id): \Drupal\Core\Entity\EntityInterface|\Drupal\Core\Entity\EntityBase|null {

    if (!is_numeric($id)) {
      $database = \Drupal::database();
      $sql = "SELECT id FROM mail_field_data where type = '" . $id . "'";
      $result = $database->query($sql);
      if ($result) {
        while ($row = $result->fetchAssoc()) {
          $id = $row['id'];
        }
      }
    }
    return parent::load($id);
  }


  /**
   * Obtener mensaje.
   *
   * @return mixed
   */
  public function getBody(): mixed {
    return $this->get('body')->value;
  }

  /**
   * Obtener asunto.
   *
   * @return mixed
   */
  public function getSubject(): mixed {
    return $this->get('subject')->value;
  }

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Type'))
      ->setSettings([
        'max_length' => 60,
        'text_processing' => 0,
        'allowed_values' => self::getTypes(),
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 3,
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 1,
      ])
      ->setRequired(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['subject'] = BaseFieldDefinition::create('string')
      ->setLabel('Asunto')
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


    $fields['body'] = BaseFieldDefinition::create('text_long')
      ->setLabel('Mensaje')
      ->setSettings([
        'default_value' => '',
      ])
      ->setDisplayOptions('form', [
        'type' => 'text_format',
        'format' => 'html_basico',
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setRequired(TRUE);

    return $fields;
  }

}

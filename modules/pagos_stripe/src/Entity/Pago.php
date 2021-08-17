<?php


namespace Drupal\pagos_stripe\Entity;


use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

/**
 * Entidad pago.
 *
 * @ingroup pago
 *
 * @ContentEntityType(
 *   id = "pago",
 *   label = "Pago",
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data" = "Drupal\pagos_stripe\Entity\PagoViewsData",
 *     "access" = "Drupal\pagos_stripe\Access\PagoAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\pagos_stripe\Routing\PagoHtmlRouteProvider"
 *      }
 *   },
 *   base_table = "pagos",
 *   admin_permission = "administer pago entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode"
 *   },
 *   field_ui_base_route = "pago.settings"
 * )
 */
class Pago extends ContentEntityBase {


  /**
   * Obtener usuario.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface|null
   */
  public function getUser() {
    $user = NULL;
    if ($this->get('usuario')->target_id) {
      $user = User::load($this->get('usuario')->target_id);
    }
    return $user;
  }

  /**
   * Obtener id entidad referenciada.
   *
   * @return null|int
   */
  public function getEntityId(): ?int {
    $id = NULL;
    if ($this->hasField('field_entidad')) {
      $id = $this->get('field_entidad')->target_id;
    }
    return $id;
  }

  /**
   * Etiqueta de la entidad relacionada.
   *
   * @return string|null
   */
  public function entityLabel(): ?string {
    $label = '';
    if ($id = $this->getEntityId()) {
      $label =  $this->get('field_entidad')->getEntity()->label();
    }
    return $label;
  }

  /**
   * Obtener pago relacionado de la entidad.
   *
   * @param int $entity_id
   *
   * @return array|null
   */
  public static function getEntityPago(int $entity_id): ?array {
    $pago = NULL;
    try {
      $pago = \Drupal::entityTypeManager()
        ->getStorage('pago')
        ->loadByProperties(['field_entidad', $entity_id]);
      if ($pago) {
        $pago = reset($pago);
      }
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
      \Drupal::service('logger.channel.pagos_stripe')->error($e->getMessage());
    }

    return $pago;

  }

  /**
   * Guardar id de la suscripción en la entidad.
   *
   * @param $subscription_id
   *  Id. de la suscripción en Stripe.
   */
  public function setSuscriptionEntity($subscription_id) {
    if ($this->hasField('field_entidad')) {
      if ($this->get('field_entidad')->target_id) {
        $entidad = $this->get('field_entidad')->entity;
        if ($entidad instanceof ContentEntityBase) {
          if ($entidad->hasField('field_suscripcion_id')) {
            $entidad->set('field_suscripcion_id', $subscription_id);
            try {
              $entidad->save();
            }
            catch (EntityStorageException $e) {
              \Drupal::service('logger.channel.pagos_stripe')->error($e->getMessage());
            }
          }
        }
      }
    }
  }

  /**
   * Guardar id del pago.
   *
   * @param $remote_id
   */
  public function setRemoteIdEntity($remote_id) {
    if ($this->hasField('field_entidad')) {
      if ($this->get('field_entidad')->target_id) {
        $entidad = $this->get('field_entidad')->entity;
        if ($entidad instanceof ContentEntityBase) {
          if ($entidad->hasField('remote_id')) {
            $entidad->set('remote_id', $remote_id);
            try {
              $entidad->save();
            }
            catch (EntityStorageException $e) {
              \Drupal::service('logger.channel.pagos_stripe')->error($e->getMessage());
            }
          }
        }
      }
    }
  }

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['usuario'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel('Usuario')
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default');

    $fields['mail'] = BaseFieldDefinition::create('email')
      ->setLabel('Email');

    $fields['concepto'] = BaseFieldDefinition::create('string')
      ->setLabel('Concepto');

    $fields['cantidad'] = BaseFieldDefinition::create('decimal')
      ->setLabel('Cantidad')
      ->setDescription(t('Cantidad total del pago realizado'))
      ->setDefaultValue(1);

    $fields['total'] = BaseFieldDefinition::create('decimal')
      ->setLabel('Total')
      ->setDescription(t('Importe total del pago realizado'))
      ->setDefaultValue(0);

    $fields['payment_intent'] = BaseFieldDefinition::create('string')
      ->setLabel('PaymentIntent')
      ->setDescription('Id. del pago en stripe');

    $fields['price'] = BaseFieldDefinition::create('string')
      ->setLabel('Price')
      ->setDescription('Id. del precio(producto) en stripe');

    $fields['subscription'] = BaseFieldDefinition::create('string')
      ->setLabel('Subscription')
      ->setDescription('Id. de la suscripción en stripe');

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('Fecha de creación'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('Fecha de actualización'));

    $fields['data'] = BaseFieldDefinition::create('map')
      ->setLabel('Datos');

    return $fields;
  }
}

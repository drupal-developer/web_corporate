<?php


namespace Drupal\pagos_stripe\Plugin\views\field;


use Drupal\pagos_stripe\Entity\Pago;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Mostrar etiqueta de la entidad relacionada.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("pago_related_entity_views_field")
 */
class PagoRelatedEntityViewsField extends FieldPluginBase {
  /**
   * {@inheritdoc}
   */
  public function query() {
    // Do nothing -- to override the parent query.
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $label = '';

    if (isset($values->_entity)) {
      $entity = $values->_entity;
      if ($entity instanceof Pago) {
        $label = $entity->entityLabel();
      }
    }

    return $label;
  }


}

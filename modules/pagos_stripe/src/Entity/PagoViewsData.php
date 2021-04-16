<?php


namespace Drupal\pagos_stripe\Entity;


use Drupal\views\EntityViewsData;

class PagoViewsData extends EntityViewsData {

  public function getViewsData() {
    $data =  parent::getViewsData();
    $data['pagos']['related_entity'] = [
      'title' => 'Entidad relacionada',
      'help' => 'Entidad relacionada con la suscripción',
      'field' => ['id' => 'pago_related_entity_views_field']
    ];
    return $data;
  }


}

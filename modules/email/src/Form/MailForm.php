<?php


namespace Drupal\email\Form;


use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use JetBrains\PhpStorm\ArrayShape;

class MailForm extends ContentEntityForm {



  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    /** @var \Drupal\email\Entity\Mail $entity */
    $entity = $this->entity;
    $form = parent::buildForm($form, $form_state);

    if (!$entity->isNew()) {
      $form['key']['#access'] = FALSE;
    }

    $form['body']['tokens'] = $this->getTokens($entity->get('key')->value);

    $form['actions']['submit']['#value'] = 'Guardar';

    return $form;
  }

  /**
   * @inheritDoc
   */
  public function validateForm(array &$form, FormStateInterface $form_state): ContentEntityInterface {
    parent::validateForm($form, $form_state);
    /** @var \Drupal\email\Entity\Mail $entity */
    $entity = $this->entity;
    if ($entity->isNew()) {
      $values = $form_state->getValues();
      $key = $values['key'][0]['value'];

      $query = \Drupal::database()->select('mail_field_data', 'm')->fields('m', ['id'])->condition('key', $key);
      $result = $query->execute();
      if ($fil = $result->fetchAssoc()) {
        $form_state->setErrorByName('key', 'Ya existe un correo con el key ' . $key);
      }
    }

    return $entity;
  }


  /**
   * @inheritDoc
   */
  public function save(array $form, FormStateInterface $form_state): int {
    \Drupal::state()->set('base_url', \Drupal::request()->getSchemeAndHttpHost());
    return parent::save($form, $form_state);
  }


  /**
   * Obtener tokens disponibles.
   *
   * @param $tipo
   *
   * @return string[]
   */
  #[ArrayShape([
    '#theme' => "string",
    '#title' => "string",
    '#list_type' => "string",
    '#items' => "array"
  ])] private function getTokens($tipo): array {

    $tokens = [
      '#theme' => 'item_list',
      '#title' => 'Tokens de reemplazo disponibles',
      '#list_type' => 'ul',
    ];

    $items = [];

    switch ($tipo) {
      case 'order_completed':
      case 'order_sent':
        $items[] = '[commerce_order:order_number] => Número de pedido';
        $items[] = '[commerce_order:mail] => Correo del cliente';
        $items[] = '[commerce_order:shipments:entity:shipping_profile:entity:address:given_name] => Nombre del cliente';
        $items[] = '[commerce_order:shipments:entity:shipping_profile:entity:address:family_name] => Apellidos del cliente';
        $items[] = '[resumen] => Resumen pedido';
        $items[] = '[datos_envio] => Datos del envío';
        $moduleHandler = \Drupal::service('module_handler');
        if ($moduleHandler->moduleExists('commerce_sendcloud')) {
          $items[] = '[tracking_url] => Url de tracking';
        }
        break;
      case 'order_completed_store':
        $items[] = '[commerce_order:order_number] => Número de pedido';
        $items[] = '[commerce_order:mail] => Correo del cliente';
        $items[] = '[commerce_order:shipments:entity:shipping_profile:entity:address:given_name] => Nombre del cliente';
        $items[] = '[commerce_order:shipments:entity:shipping_profile:entity:address:family_name] => Apellidos del cliente';
        $items[] = '[commerce_order:admin-url] => Url administración';
        $items[] = '[resumen] => Resumen pedido';
        $items[] = '[datos_envio] => Datos del envío';
        break;
      case 'stock_alert':
      case 'stock_alert_complete':
        $items[] = '[alerta:producto:entity:product_id:entity:title] => Producto';
        $items[] = '[alerta:producto:entity:product_id:entity:url] => Url producto';
        $items[] = '[alerta:usuario:entity:field_nombre] => Nombre del usuario';
        $items[] = '[alerta:email] => Email de la alerta';
        break;
      default:
        $items[] = '[user:name] => Nombre del usuario';
        $items[] = '[user:mail] => Email del usuario';
        $items[] = '[user:one-time-login-url] => Enlace de inicio de sesión único para establecer contraseña';
    }

    $items[] = '[site:name] => Nombre del sitio';
    $items[] = '[site:login-url] => Url de acceso';

    $tokens['#items'] = $items;

    return $tokens;
  }

}

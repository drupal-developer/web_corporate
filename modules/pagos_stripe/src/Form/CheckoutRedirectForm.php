<?php


namespace Drupal\pagos_stripe\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\pagos_stripe\Entity\Pago;

class CheckoutRedirectForm extends FormBase {

  /**
   * @var mixed|null
   */
  protected $entity;

  /**
   * @inheritDoc
   */
  public function getFormId(): string {
    return 'checkout_redirect_form';
  }


  /**
   * CheckoutRedirectForm constructor.
   */
  public function __construct() {
    $this->entity = $this->getRouteMatch()->getParameter('pago');
  }

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = [];
    $entity = $this->entity;
    if ($entity instanceof Pago) {
      if (!$entity->get('payment_intent')->value) {
        $session_id = \Drupal::service('pagos_stripe.stripe_api')->createSession($entity);
        if ($session_id) {
          $apiKey = \Drupal::service('pagos_stripe.stripe_api')->pubKey;
          $form['api_key'] = [
            '#type' => 'hidden',
            '#attributes' => ['class' => ['apikey']],
            '#default_value' => $apiKey,
          ];
          $form['session_id'] = [
            '#type' => 'hidden',
            '#attributes' => ['class' => ['sessionId']],
            '#default_value' => $session_id,
          ];

          $form['mensaje']['#markup'] = 'Redirigiendo al TPV...';

          $form['#attached']['library'][] = 'pagos_stripe/checkout';
        }
      }
      else {
        $form['error']['#markup'] = 'El pago ya esta procesado.';
      }
    }

    if (empty($form)) {
      $form['error']['#markup'] = 'No se ha podido crear la sesión. Inténtelo de nuevo.';
    }

    return $form;
  }

  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

}

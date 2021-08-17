<?php

namespace Drupal\pagos_stripe\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\pagos_stripe\StripeApi;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TpvForm extends \Drupal\Core\Form\FormBase {

  /**
   * @var \Drupal\pagos_stripe\StripeApi
   */
  private StripeApi $stripeApi;

  /**
   * @var mixed|null
   */
  private $ref;

  /**
   * @var mixed|null
   */
  private $amount;

  /**
   * @param \Drupal\pagos_stripe\StripeApi $stripeApi
   */
  public function __construct(StripeApi $stripeApi) {
    $this->stripeApi = $stripeApi;
    $this->ref = $this->getRouteMatch()->getParameter('ref');
    $this->amount = $this->getRouteMatch()->getParameter('amount');
  }

  public static function create(ContainerInterface $container): TpvForm {
    return new static(
      $container->get('pagos_stripe.stripe_api'),
    );
  }


  /**
   * @inheritDoc
   */
  public function getFormId() {
    return 'tpv_form';
  }

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $session_id = $this->stripeApi->createSessionTPV($this->ref, $this->amount);
    if ($session_id) {
      $apiKey = $this->stripeApi->pubKey;
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
    else {
      $form['error']['#markup'] = 'No se ha podido crear la sesión. Inténtelo de nuevo.';
    }

    return $form;
  }

  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitForm() method.
  }

}

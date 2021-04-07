<?php


namespace Drupal\pagos_stripe\Form;


use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\pagos_stripe\StripeApi;
use Stripe\Plan;
use Stripe\Product;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configuración correos.
 *
 * @ingroup mail
 */
class PagoSettingsForm extends ConfigFormBase {

  /**
   * @var \Drupal\pagos_stripe\StripeApi
   */
  protected StripeApi $stripeApi;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\pagos_stripe\StripeApi $stripeApi
   */
  public function __construct(ConfigFactoryInterface $config_factory, StripeApi $stripeApi) {
    parent::__construct($config_factory);
    $this->stripeApi = $stripeApi;
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container): PagoSettingsForm {
    return new static(
      $container->get('config.factory'),
      $container->get('pagos_stripe.stripe_api')
    );
  }

  /**
   * @inheritDoc
   */
  protected function getEditableConfigNames(): array {
    return ['pagos_stripe.settings'];
  }


  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'pago_settings';
  }



  /**
   * Defines the settings form for Pago.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Form definition array.
   * @throws \Stripe\Exception\ApiErrorException
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('pagos_stripe.settings');
    $planes = $this->stripeApi->getPlans();
    $options = [];
    foreach ($planes as $plan) {
      if ($plan instanceof Plan) {
        $product = Product::retrieve($plan->product);
        $importe = $plan->amount_decimal / 100;
        $options[$plan->id] = $product->name . ' ' . $importe . ' €/' . $plan->interval;
      }
    }

    if (!empty($options)) {
      $form['planes'] = [
        '#type' => 'checkboxes',
        '#title' => 'Planes',
        '#options' => $options,
        '#default_value' => $config->get('planes') ? $config->get('planes') : [],
      ];
    }

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => 'Guardar',
      ],
    ];

    return $form;
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('pagos_stripe.settings')->set('planes', $values['planes'])->save();
    $this->messenger()->addStatus('Configuración guardada');
  }

}

<?php


namespace Drupal\pagos_stripe;


use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Url;
use Drupal\pagos_stripe\Entity\Pago;
use Drupal\stripe_api\StripeApiService;
use Drupal\user\Entity\User;
use Psr\Log\LoggerInterface;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Plan;
use Stripe\Price;
use Stripe\Product;
use Stripe\Service\SubscriptionItemService;
use Stripe\Service\SubscriptionService;
use Stripe\Subscription;
use Stripe\SubscriptionItem;

class StripeApi {

  /**
   * @var \Drupal\stripe_api\StripeApiService
   */
  protected StripeApiService $stripeApi;

  /**
   * @var \Psr\Log\LoggerInterface
   */
  public LoggerInterface $logger;

  public ?string $apiKey;

  public ?string $pubKey;

  /**
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected ConfigFactory $config;


  /**
   * StripeApi constructor.
   *
   * @param \Drupal\stripe_api\StripeApiService $stripeApiService
   * @param \Psr\Log\LoggerInterface $logger
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   */
  public function __construct(StripeApiService $stripeApiService, LoggerInterface $logger, ConfigFactory $configFactory) {
    $this->stripeApi = $stripeApiService;
    $this->logger = $logger;
    $this->apiKey = $stripeApiService->getApiKey();
    $this->pubKey = $stripeApiService->getPubKey();
    $this->config = $configFactory;
  }

  /**
   * Obtener todos los planes disponibles.
   *
   * @return array|null
   */
  public function getPlans(): ?array {
    $planes = NULL;
    try {
      $list = Plan::all(['active' => TRUE]);
      foreach ($list->getIterator() as $item) {
        $planes[] = $item;
      }
    }
    catch (ApiErrorException $e) {
      $this->logger->error($e->getMessage());
    }
    return $planes;
  }

  /**
   * Crear sesión pago.
   *
   * @param \Drupal\pagos_stripe\Entity\Pago $pago
   * @return string|null
   */
  public function createSession(Pago $pago): ?string {
    $urlok = NULL;
    $urlKo = NULL;
    $dataPago = $pago->get('data')->getValue();
    if (!empty($dataPago)) {
      $dataPago = reset($dataPago);
      if (isset($dataPago['urlko'])) {
        $urlKo = Url::fromUri('internal:' . $dataPago['urlko'], ['absolute' => TRUE, 'query' => ['r' => 'ko']]);
      }
      if (isset($dataPago['urlok'])) {
        $urlok = Url::fromUri('internal:' . $dataPago['urlok'], ['absolute' => TRUE, 'query' => ['r' => 'ok']]);
      }
    }

    $id_session = NULL;
    if ($this->apiKey && $urlKo instanceof Url && $urlok instanceof Url) {

      $usuario = $pago->getUser();
      $price_id = $pago->get('price')->value;
      $price = NULL;
      if ($price_id) {
        try {
          $price = Price::retrieve($price_id);
        }
        catch (ApiErrorException $e) {
          $this->logger->error($e->getMessage());
        }
      }

      if ($price instanceof Price) {
        $mode = $price->type == 'recurring' ? 'subscription' : 'payment';
        if (isset($dataPago['mode'])) {
          $mode = $dataPago['mode'];
        }
        $product = NULL;
        try {
          $product = Product::retrieve($price->product);
        }
        catch (ApiErrorException $e) {
          $this->logger->error($e->getMessage());
        }

        $datos_session = [
          'success_url' => $urlok->toString(),
          'cancel_url' => $urlKo->toString(),
          'payment_method_types' => ['card'],
          'line_items' => [
            [
              'price' => $price->id,
              'quantity' => (int) $pago->get('cantidad')->value,
            ],
          ],
          'mode' => $mode,
          'metadata' => ['pago' => $pago->id()]
        ];

        if ($mode == 'setup') {
          unset($datos_session['line_items']);

          if ($usuario instanceof User) {

            if (!$usuario->get('stripe_customer_id')->value) {
              $nombre = '';
              if ($usuario->get('field_nombre')->value) {
                $nombre = $usuario->get('field_nombre')->value;
                if ($usuario->get('field_apellidos')->value) {
                  $nombre .= ' ' . $usuario->get('field_apellidos')->value;
                }
              }

              $site_config = $this->config->get('system.site');

              try {
                $customer = Customer::create([
                  'email' => $usuario->getEmail(),
                  'description' => $site_config->get('name'),
                  'name' => $nombre,
                ]);
                $usuario->set('stripe_customer_id', $customer->id);
              }
              catch (ApiErrorException $e) {
                $this->logger->error($e->getMessage());
              }
            }
          }
        }

        if (isset($dataPago['trial'])) {
          $datos_session['metadata']['trial'] = $dataPago['trial'];
        }
        $datos_session['metadata']['price'] = $price->id;

        if ($usuario instanceof User) {
          if ($usuario->get('stripe_customer_id')->value) {
            $datos_session['customer'] = $usuario->get('stripe_customer_id')->value;
          }
          else {
            $datos_session['customer_email'] = $usuario->getEmail();
          }
          $datos_session['metadata']['user'] = $usuario->id();
        }

        if ($product instanceof Product && $mode == 'payment') {
          $datos_session['payment_intent_data']['description'] = $pago->get('concepto')->value;
        }

        try {
          $session = Session::create($datos_session);
          $id_session = $session->id;
        }
        catch (ApiErrorException $e) {
          $this->logger->error($e->getMessage());
        }
      }
    }

    return $id_session;
  }

  /**
   * Obtener suscripción.
   *
   * @param $subscription_id
   *
   * @return array|\Stripe\Subscription|null
   */
  public function getSubscription($subscription_id) {
    $subscrtiption = NULL;
    try {
      $subscrtiption = Subscription::retrieve($subscription_id);
    }
    catch (ApiErrorException $e) {
      $this->logger->error($e->getMessage());
    }

    if ($subscrtiption) {
      $subscrtiption = $subscrtiption->toArray();
    }

    return $subscrtiption;
  }

  /**
   * Actualizar cantidad de una suscripción.
   *
   * @param $subscription_id
   * @param $quantity
   *
   * @return bool
   */
  public function updateQuantityItemSuscription($subscription_id, $quantity): bool {
    $subscrtiption = $this->getSubscription($subscription_id);
    $response = FALSE;
    if ($subscrtiption) {
      if (isset($subscrtiption['items']['data'][0]['id'])) {
        $item_suscripcion_id = $subscrtiption['items']['data'][0]['id'];
        try {
          SubscriptionItem::update($item_suscripcion_id, ['quantity' => $quantity, 'proration_behavior' => 'always_invoice']);
          $response = TRUE;
          $this->logger->info('Suscripcion ' . $subscription_id . ' actualizada');
        }
        catch (ApiErrorException $e) {
          $this->logger->error($e->getMessage());
        }
      }
    }

    return $response;
  }

  /**
   * Cancelar suscripción.
   *
   * @param $subscription_id
   */
  public function cancelSubscription($subscription_id) {
    $subscription = NULL;
    try {
      $subscription = Subscription::retrieve($subscription_id);
    }
    catch (ApiErrorException $e) {
      $this->logger->error($e->getMessage());
    }

    if ($subscription instanceof Subscription) {
      try {
        $subscription->cancel();
        $this->logger->info('Suscripción ' . $subscription_id . ' cancelada.');
      }
      catch (ApiErrorException $e) {
        $this->logger->error($e->getMessage());
      }
    }
  }

  public function getPrecio() {
    $planes = $this->getPlans();
    $importe = 0;
    foreach ($planes as $plan) {
      if ($plan instanceof Plan) {
        $importe = $plan->amount_decimal / 100;
        break;
      }
    }

    return $importe;
  }

}

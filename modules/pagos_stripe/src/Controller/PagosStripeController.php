<?php


namespace Drupal\pagos_stripe\Controller;


use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\pagos_stripe\Entity\Pago;
use Drupal\pagos_stripe\StripeApi;
use Drupal\user\Entity\User;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;
use Stripe\PaymentIntent;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PagosStripeController extends ControllerBase {


  /**
   * @var \Drupal\pagos_stripe\StripeApi
   */
  protected StripeApi $stripeApi;

  public function __construct(StripeApi $stripeApi) {
    $this->stripeApi = $stripeApi;
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container): PagosStripeController {
    return new static(
      $container->get('pagos_stripe.stripe_api')
    );
  }

  /**
   * Página notificación checkout Stripe.
   *
   * @return string[]
   */
  public function checkoutNotification(): array {
    $payload = @file_get_contents('php://input');
    $event = Json::decode($payload);
    $data = $event['data']['object'];

    $config = $this->config('pagos_stripe.settings');
    $planes = $config->get('planes');
    $access = FALSE;
    $precio = NULL;
    if (isset($data['subscription'])) {
      if ($subscription = $this->stripeApi->getSubscription($data['subscription'])) {
        $precio = $subscription['plan']['id'];
      }
    }
    elseif (isset($data['invoice'])) {
      $invoice = NULL;
      try {
        $invoice = Invoice::retrieve($data['invoice']);
      }
      catch (ApiErrorException $e) {
        $this->stripeApi->logger->error($e->getMessage());
      }
      if ($invoice instanceof Invoice) {
        if ($invoice->subscription) {
          if ($subscription = $this->stripeApi->getSubscription($invoice->subscription)) {
            $precio = $subscription['plan']['id'];
          }
        }
      }
    }

    if ($precio && in_array($precio, $planes)) {
      $access = TRUE;
    }


    if ($access) {
      if ($data['object'] == 'checkout.session') {
        $site_config = $this->config('system.site');
        $customer_data = ['description' => $site_config->get('name')];
        if (isset($data['metadata']['user'])) {
          $usuario = User::load($data['metadata']['user']);
          if ($usuario instanceof User) {
            if ($usuario->hasField('stripe_customer_id')) {
              $usuario->set('stripe_customer_id', $data['customer']);
              $nombre = '';
              if ($usuario->get('field_nombre')->value) {
                $nombre = $usuario->get('field_nombre')->value;
                if ($usuario->get('field_apellidos')->value) {
                  $nombre .= ' ' . $usuario->get('field_apellidos')->value;
                }
              }

              if ($nombre != '') {
                $customer_data['name'] = $nombre;
              }

              try {
                $usuario->save();
              }
              catch (EntityStorageException $e) {
                $this->stripeApi->logger->error($e->getMessage());
              }
            }
          }
        }

        try {
          Customer::update($data['customer'],$customer_data);
        }
        catch (ApiErrorException $e) {
          $this->stripeApi->logger->error($e->getMessage());
        }


        if (isset($data['metadata']['pago'])) {
          $pago = Pago::load($data['metadata']['pago']);
          if ($pago instanceof Pago) {
            if (isset($data['subscription'])) {
              $pago->set('subscription', $data['subscription']);
              $pago->setSuscriptionEntity($data['subscription']);
            }
            try {
              $pago->save();
            }
            catch (EntityStorageException $e) {
              $this->stripeApi->logger->error($e->getMessage());
            }
          }
        }
      }
      elseif ($data['object'] == 'payment_intent') {
        if (isset($data['invoice'])) {
          $invoice = NULL;
          try {
            $invoice = Invoice::retrieve($data['invoice']);
          }
          catch (ApiErrorException $e) {
            $this->stripeApi->logger->error($e->getMessage());
          }

          if ($invoice instanceof Invoice) {
            if ($invoice->subscription) {
              $pagos = NULL;
              try {
                $pagos = $this->entityTypeManager()
                  ->getStorage('pago')
                  ->loadByProperties(['subscription' => $invoice->subscription]);
              }
              catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
                $this->stripeApi->logger->error($e->getMessage());
              }
              if ($pagos) {
                $new = TRUE;
                $pago_anterior = NULL;
                foreach ($pagos as $pago) {
                  if ($pago instanceof Pago) {
                    if ($pago->get('payment_intent')->value) {
                      $pago_anterior = $pago;
                    }
                    else {
                      $total = round($data['amount'] / 100, 2);
                      $pago->set('payment_intent', $data['id']);
                      $pago->set('total', $total);
                      try {
                        PaymentIntent::update($data['id'], ['description' => $pago->get('concepto')->value]);
                      }
                      catch (ApiErrorException $e) {
                        $this->stripeApi->logger->error($e->getMessage());
                      }
                      try {
                        $new = FALSE;
                        $pago->save();
                      }
                      catch (EntityStorageException $e) {
                        $this->stripeApi->logger->error($e->getMessage());
                      }
                    }
                  }
                }

                if ($new && $pago_anterior) {
                  $cantidad = $pago_anterior->get('cantidad')->value;
                  $total = $pago_anterior->get('total')->value;
                  $concepto = $pago_anterior->get('concepto')->value;
                  $pago = Pago::create([
                    'usuario' => $pago_anterior->get('usuario')->target_id,
                    'cantidad' => $cantidad,
                    'price' => $pago_anterior->get('price')->value,
                    'field_entidad' => $pago_anterior->get('field_entidad')->target_id,
                    'concepto' => $concepto,
                    'total' => $total,
                    'payment_intent' => $data['id'],
                  ]);

                  try {
                    $pago->save();
                  }
                  catch (EntityStorageException $e) {
                    \Drupal::service('logger.channel.qrplus')->error($e->getMessage());
                  }

                  try {
                    PaymentIntent::update($data['id'], ['description' => $pago_anterior->get('concepto')->value]);
                  }
                  catch (ApiErrorException $e) {
                    $this->stripeApi->logger->error($e->getMessage());
                  }

                }
              }
            }
          }
        }
      }
    }



    return ['#markup' => ''];
  }

}

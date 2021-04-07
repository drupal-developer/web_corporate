<?php


namespace Drupal\corporate\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use JetBrains\PhpStorm\ArrayShape;

class CorporateController extends ControllerBase {

  /**
   * Página entidades.
   *
   * @return string[]
   */
  #[ArrayShape(['#type' => "string", '#markup' => "string"])] public function entidadesPage(): array {
    return [
      '#type' => 'markup',
      '#markup' => 'Entidades',
    ];
  }
  /**
   * Página bloques.
   *
   * @return string[]
   */
  #[ArrayShape(['#type' => "string", '#markup' => "string"])] public function bloques(): array {
    return [
      '#type' => 'markup',
      '#markup' => 'Bloques',
    ];
  }

  /**
   * Página inicio.
   *
   * @return TrustedRedirectResponse
   */
  public function front(): TrustedRedirectResponse {
    $response = new TrustedRedirectResponse('/');
    return $response->send();
  }


}

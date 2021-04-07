<?php


namespace Drupal\redes\Controller;


use Drupal\Core\Entity\Controller\EntityViewController;
use Drupal\redes\Entity\RedSocial;

class RedSocialViewController extends EntityViewController {

  /**
   * Titulo de las páginas de administración de redes sociales.
   *
   * @param \Drupal\redes\Entity\RedSocial|null $red
   *
   * @return string
   */
  public function title(RedSocial $red = NULL): string {
    $title = 'Añadir red social';
    if ($red) {
      $title = 'Editar red social ' . $red->label();
    }
    return $title;
  }

}

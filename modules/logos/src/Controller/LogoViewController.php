<?php


namespace Drupal\logos\Controller;


use Drupal\Core\Entity\Controller\EntityViewController;
use Drupal\logos\Entity\Logo;

class LogoViewController extends EntityViewController {

  /**
   * Titulo de las páginas de administración de logos.
   *
   * @param \Drupal\logos\Entity\Logo|null $logo
   *
   * @return string
   */
  public function title(Logo $logo = NULL): string {
    $title = 'Añadir logo';
    if ($logo) {
      $types = Logo::getTypes();
      $title = 'Editar logo de ' . $types[$logo->get('type')->value] ;
    }
    return $title;
  }

}

<?php


namespace Drupal\logos\Routing;


use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Symfony\Component\Routing\RouteCollection;

class LogoHtmlRouteProvider extends AdminHtmlRouteProvider {
  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type): array|RouteCollection {
    $collection = parent::getRoutes($entity_type);

    $defaults = $collection->get('entity.logo.edit_form')->getDefaults();
    $defaults['_title_callback'] = '\Drupal\logos\Controller\LogoViewController::title';
    $collection->get('entity.logo.edit_form')->addDefaults($defaults);

    return $collection;
  }
}

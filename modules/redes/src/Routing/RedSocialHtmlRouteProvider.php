<?php


namespace Drupal\redes\Routing;


use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Symfony\Component\Routing\RouteCollection;

class RedSocialHtmlRouteProvider extends AdminHtmlRouteProvider {
  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type): array|RouteCollection {
    $collection = parent::getRoutes($entity_type);

    $defaults = $collection->get('entity.red_social.edit_form')->getDefaults();
    $defaults['_title_callback'] = '\Drupal\redes\Controller\RedSocialViewController::title';
    $collection->get('entity.red_social.edit_form')->addDefaults($defaults);

    return $collection;
  }
}

<?php


namespace Drupal\corporate\Routing;


use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

class RouteSubscriber extends RouteSubscriberBase {

  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('system.admin_config')) {
      $route->setRequirement('_permission', 'access site configuration');
    }

    if ($route = $collection->get('system.admin_structure')) {
      $route->setRequirement('_permission', 'access structure');
    }

    if ($route = $collection->get('entity.block_content.edit_form')) {
      $requirements = $route->getRequirements();
      if (isset($requirements['_entity_access'])) {
        unset($requirements['_entity_access']);
      }


      if (isset($requirements['block_content'])) {
        unset($requirements['block_content']);
      }
      $requirements['_permission'] = 'edit block content';
      $route->setRequirements($requirements);
    }
  }

}

<?php


namespace Drupal\redes\Access;


use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Access\AccessResultNeutral;
use Drupal\Core\Access\AccessResultReasonInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

class RedSocialAccessControlHandler extends EntityAccessControlHandler {
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account): AccessResultReasonInterface|AccessResultNeutral|AccessResult|AccessResultAllowed|AccessResultInterface {

    if (AccessResult::allowedIfHasPermission($account, 'administer red_social entities')) {
      return AccessResult::allowed();
    }

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view red_social entities');
      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit red_social entities');
      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'administer red_social entities');
    }

    return AccessResult::neutral();
  }

}

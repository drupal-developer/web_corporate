<?php


namespace Drupal\logos\Access;


use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Access\AccessResultNeutral;
use Drupal\Core\Access\AccessResultReasonInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

class LogoAccessControlHandler extends EntityAccessControlHandler {
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account): AccessResultReasonInterface|AccessResultForbidden|AccessResultNeutral|AccessResult|AccessResultAllowed|AccessResultInterface {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view logo entities');
      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit logo entities');
      case 'delete':
        if (\Drupal::currentUser()->id() == 1) {
          return AccessResult::allowed();
        }
        else {
          return AccessResult::forbidden();
        }
    }

    return AccessResult::neutral();
  }

}

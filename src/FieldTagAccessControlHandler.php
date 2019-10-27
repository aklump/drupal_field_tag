<?php

namespace Drupal\field_tag;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Field tag entity.
 *
 * @see \Drupal\field_tag\Entity\FieldTag.
 */
class FieldTagAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\field_tag\Entity\FieldTagInterface $entity */

    switch ($operation) {

      case 'view':

        return AccessResult::allowedIfHasPermission($account, 'view published field tag entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit field tag entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete field tag entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add field tag entities');
  }


}

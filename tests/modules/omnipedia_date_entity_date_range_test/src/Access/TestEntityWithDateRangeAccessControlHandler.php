<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date_entity_date_range_test\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the TestEntityWithDateRange entity.
 */
class TestEntityWithDateRangeAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(
    EntityInterface $entity, $operation, AccountInterface $account
  ) {

    /** @var string|bool */
    $adminPermission = $this->entityType->getAdminPermission();

    if ($account->hasPermission($adminPermission)) {
      return AccessResult::allowed();
    }

    switch ($operation) {

      case 'view':
        return AccessResult::allowedIfHasPermission(
          $account, 'view test_entity_with_date_range entity'
        );

      case 'update':
        return AccessResult::allowedIfHasPermission(
          $account, 'edit test_entity_with_date_range entity'
        );

      case 'delete':
        return AccessResult::allowedIfHasPermission(
          $account, 'delete test_entity_with_date_range entity'
        );

    }

    return AccessResult::neutral();

  }

  /**
   * {@inheritdoc}
   *
   * Separate from the checkAccess because the entity does not yet exist. It
   * will be created during the 'add' process.
   */
  protected function checkCreateAccess(
    AccountInterface $account, array $context, $entityBundle = null
  ) {

    /** @var string|bool */
    $adminPermission = $this->entityType->getAdminPermission();

    // Admin permission overrides all others.
    if ($account->hasPermission($adminPermission)) {
      return AccessResult::allowed();
    }

    return AccessResult::allowedIfHasPermission(
      $account, 'add test_entity_with_date_range entity'
    );

  }

}

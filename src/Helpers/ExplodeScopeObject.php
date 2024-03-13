<?php

namespace Drupal\field_tag\Helpers;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;

class ExplodeScopeObject {

  /**
   * @param object|null $scope_object
   *
   * @return array
   *   - 0 ?\Drupal\Core\Entity\EntityInterface
   *   - 1 ?\Drupal\Core\Field\FieldItemListInterface
   *   - 2 ?\Drupal\Core\Field\FieldItemInterface
   */
  public function __invoke(?object $scope_object): array {
    if (NULL === $scope_object) {
      return [NULL, NULL, NULL];
    }
    elseif ($scope_object instanceof EntityInterface) {
      return [$scope_object, NULL, NULL];
    }
    elseif ($scope_object instanceof FieldItemListInterface) {
      return [$scope_object->getParent()->getEntity(), $scope_object, NULL];
    }
    elseif ($scope_object instanceof FieldItemInterface) {
      $field_item_list = $scope_object->getParent();

      return [
        $field_item_list->getParent()->getEntity(),
        $field_item_list,
        $scope_object,
      ];
    }
    else {
      throw new \InvalidArgumentException(sprintf('Unknown scope object of type: %s', get_class($scope_object)));
    }
  }

}

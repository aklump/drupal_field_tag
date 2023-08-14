<?php

namespace Drupal\field_tag\Plugin\Validation\Violations;

use Drupal\Core\Entity\EntityInterface;

abstract class ViolationBase {

  private $data;

  /**
   * @param array $rule
   * @param int $scope
   *   See below for constants.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @see \Drupal\field_tag\Plugin\Validation\Constraint\FieldTagConstraint::SCOPE_ENTITY
   * @see \Drupal\field_tag\Plugin\Validation\Constraint\FieldTagConstraint::SCOPE_BUNDLE
   * @see \Drupal\field_tag\Plugin\Validation\Constraint\FieldTagConstraint::SCOPE_FIELD
   */
  public function __construct(array $rule, int $scope, EntityInterface $entity) {
    $this->params = func_get_args();
  }

  public function getScope(): int {
    return $this->params[1] ?? 0;
  }

  public function getParams(): array {
    return $this->params;
  }

}

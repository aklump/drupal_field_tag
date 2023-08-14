<?php

namespace Drupal\field_tag\Helpers;

use Drupal\field_tag\Entity\FieldTagInterface;
use Drupal\field_tag\Tags;

/**
 * Get a string that can be used to compare two instances safely.
 */
final class GetComparisonValue {

  public function __invoke(FieldTagInterface $field_tag): string {
    $stack = [];
    $parent = $field_tag->getParentEntity();
    if ($parent) {
      $stack[] = $parent->getEntityTypeId();
      $stack[] = $parent->id();
    }
    $stack[] = $field_tag->getFieldName();
    $stack[] = $field_tag->getDelta();
    $stack[] = (string) Tags::create($field_tag->getValue())->sort();
    $stack = array_values(array_filter($stack));

    return json_encode($stack);
  }

}

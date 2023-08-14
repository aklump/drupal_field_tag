<?php

namespace Drupal\field_tag\Helpers;


use Drupal\field_tag\Entity\FieldTagInterface;

/**
 * Determine if two field tags are equal.
 *
 * @see \Drupal\field_tag\Helpers\GetComparisonValue
 */
final class Compare {

  public function __invoke(FieldTagInterface $a, FieldTagInterface $b): bool {
    $hash = new GetComparisonValue();

    return $hash($a) === $hash($b);
  }
}

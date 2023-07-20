<?php

namespace Drupal\field_tag\Helpers;


use Drupal\field_tag\Entity\FieldTagInterface;

/**
 * Determine if two field tags are equal.
 */
final class Compare {

  public function __invoke(FieldTagInterface $a, FieldTagInterface $b): bool {
    $hash = new ValueHash();

    return $hash($a) === $hash($b);
  }
}

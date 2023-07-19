<?php

namespace Drupal\field_tag\Helpers;


use Drupal\field_tag\Entity\FieldTagInterface;

/**
 * Determine if two field tags are equal.
 */
final class Compare {

  public function __invoke(FieldTagInterface $a, FieldTagInterface $b): bool {
    $serialize = function (FieldTagInterface $tag): string {
      return implode(':', [
        $tag->getParentEntity()->getEntityTypeId(),
        $tag->getParentEntity()->id(),
        $tag->getFieldName(),
        $tag->getDelta(),
        $tag->getValue(),
      ]);
    };
    $a = $serialize($a);
    $b = $serialize($b);

    return $a === $b;
  }
}

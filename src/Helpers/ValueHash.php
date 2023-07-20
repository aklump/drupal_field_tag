<?php

namespace Drupal\field_tag\Helpers;

use Drupal\field_tag\Entity\FieldTagInterface;

/**
 * Get a hash of the value to use for comparing two instances.
 */
final class ValueHash {

  public function __invoke(FieldTagInterface $field_tag): string {
    return md5(implode(':', [
      $field_tag->getParentEntity()->getEntityTypeId(),
      $field_tag->getParentEntity()->id(),
      $field_tag->getFieldName(),
      $field_tag->getDelta(),
      $field_tag->getValue(),
    ]));
  }

}

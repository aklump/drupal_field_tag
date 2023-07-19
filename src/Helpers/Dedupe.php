<?php

namespace Drupal\field_tag\Helpers;

final class Dedupe {

  /**
   * Remove older entities having the same field value as the newest.
   *
   * @param \Drupal\field_tag\Entity\FieldTagInterface[] $field_tag_entities
   *   The expectation is that all of these are meant to be attached to the same
   *   parent.  And the assumption is that the tag value of one with the highest
   *   entity ID (most-recently created) is the correct value.  Entities are
   *   sorted by NID ascending and then removed.  The entity with the highest ID
   *   will never be removed.
   *
   * @return \Drupal\field_tag\Entity\FieldTagInterface[]
   *   A new array made from $field_tag_entities with the older duplicates
   *   removed.  A successful deduplication is indicated by this having a count
   *   of one.
   *
   * @see \Drupal\field_tag\Helpers\Compare
   */
  public function __invoke(array $field_tag_entities): array {

    // Put them in order of oldest first, so we can keep older tags.
    usort($field_tag_entities, function ($a, $b) {
      return $a->id() - $b->id();
    });

    $comparator = new Compare();

    // We will trust the newest value as the one to retain.  Any older ones with
    // the same value will be deleted to try and clean up the duplicated
    // situation.
    $retained_value = array_pop($field_tag_entities);

    $new_stack = [$retained_value];
    while ($a = array_pop($field_tag_entities)) {
      if (!$comparator($retained_value, $a)) {
        $new_stack[] = $a;
      }
    }

    return $new_stack;
  }

}

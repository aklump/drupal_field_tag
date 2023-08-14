<?php

namespace Drupal\field_tag\Helpers;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\field_tag\Tags;

/**
 * Tally the tag usage on an entity, field or field_item.
 *
 * This serves an important function apart from \Drupal\field_tag\Tags::count()
 * because the latter class/method will always dedupe tags and therefor you
 * cannot determine how many times a single tag is actually used.  This class
 * does not remove duplicate tags.
 */
final class CountTags {

  public function __invoke(?object $tagged_object) {
    $fields = [];
    $tag_values = [];
    if ($tagged_object instanceof FieldableEntityInterface) {
      $fields = $tagged_object->getFields();
    }
    elseif ($tagged_object instanceof FieldItemListInterface) {
      $fields = [$tagged_object];
    }
    elseif ($tagged_object instanceof FieldItemInterface) {
      $value = $tagged_object->getValue()['field_tag'] ?? NULL;
      if ($value) {
        $tag_values = (new Tags($value))->all();
      }
    }
    else {
      throw new \InvalidArgumentException(sprintf('Unknown $tagged_object of type %s', get_class($tagged_object)));
    }

    // Nothing to process, let's call it a day.
    if (empty($fields) && empty($tag_values)) {
      return [];
    }

    // Note: this will not run for FieldItemInterface $tagged_object instances,
    // which has already set $tag_values by this point.
    foreach ($fields as $field_item_list) {
      foreach ($field_item_list as $item) {
        $tag_value = $item->field_tag;
        if ($tag_value) {
          $tag_values = array_merge(($tag_values ?? []), (new Tags($tag_value))->all());
        }
      }
    }

    // Normalize before counting because tags are by definition case insensitive
    // and therefor we don't want case to confuse the result.
    $tag_values = array_map('strtolower', $tag_values);
    $tag_counts = array_count_values($tag_values);
    ksort($tag_counts);

    return $tag_counts;
  }

}

<?php

namespace Drupal\field_tag\Rule;

use Drupal\field_tag\Helpers\CountTags;
use Drupal\field_tag\Helpers\ExplodeScopeObject;

/**
 * Get total tag usage for an object based on a rule.
 *
 * This is different from
 */
final class GetUsageCountByRuleMatch {

  /**
   * @var \Drupal\field_tag\Rule\Rule
   */
  private $rule;

  public function __construct(Rule $rule) {
    $this->rule = $rule;
  }

  /**
   * Sum the tag counts for those that match a rule.
   *
   * It will only count tags that match the rule.  And it will only count tags
   * on fields that are a part of the rule, in the case that there are field
   * rule conditions or requirements.
   *
   * @param object $tagged_object
   *   One of EntityInterface, FieldItemInterface, FieldItem.
   *
   * @return int[]
   *   The total tag usage that matches the given rule.
   *
   * @see \Drupal\field_tag\Helpers\CountTags
   */
  public function __invoke(object $tagged_object): int {
    $rule_fields = $this->rule->getRequiredFieldNames();

    if (!$rule_fields) {
      // If rule has no field condition or requirement, then any field is
      // eligible to be counted.  We don't care just let the counter run.
      $counts_by_tag = (new CountTags())($tagged_object);
    }

    else {
      // Otherwise we are only going to count fields that exist in the rule.
      $possible_fields_to_count = [];
      $counts_by_tag = [];

      list($entity, $field_item_list, $field_item) = (new ExplodeScopeObject())($tagged_object);
      if ($entity === $tagged_object) {
        $possible_fields_to_count = $tagged_object->getFields();
      }
      elseif ($field_item_list === $tagged_object) {
        $possible_fields_to_count = [$tagged_object];
      }
      elseif ($field_item === $tagged_object) {
        $possible_fields_to_count = [$tagged_object->getParent()];
      }
      foreach ($possible_fields_to_count as $field_item_list) {
        if (!in_array($field_item_list->getName(), $rule_fields)) {
          continue;
        }
        $field_tag_counts = (new CountTags())($field_item_list);
        foreach ($field_tag_counts as $field_tag => $field_usage_count) {
          $counts_by_tag[$field_tag] = $counts_by_tag[$field_tag] ?? 0;
          $counts_by_tag[$field_tag] += $field_usage_count;
        }
      }
    }

    // Now sum all the tag counts by only counting rule-matching tags.
    $usage_count = 0;
    $tag_matches_rule = new CheckTagAgainstRule($this->rule);
    foreach ($counts_by_tag as $some_tag_value => $tag_usage) {
      if ($tag_matches_rule($some_tag_value)) {
        $usage_count += $tag_usage;
      }
    }

    return $usage_count;
  }

}

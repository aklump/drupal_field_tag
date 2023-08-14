<?php

namespace Drupal\field_tag\Rule;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\field_tag\Helpers\CountTags;

class CheckConditions {

  public function __construct(\Drupal\field_tag\FieldTagService $field_tag_service) {
    $this->fieldTagService = $field_tag_service;
  }

  /**
   * @param \Drupal\field_tag\Rule\Rule $rule
   * @param object $scope_object
   *
   * @return bool
   *   True if all set conditions are met.
   */
  public function __invoke(Rule $rule, object $scope_object): bool {
    list($entity, $field_item_list) = (new \Drupal\field_tag\Helpers\ExplodeScopeObject())($scope_object);
    $conditions = $rule->jsonSerialize()[Rule::CONDITION] ?? [];

    // Check for tag match.
    foreach ($conditions as $criterion => $condition) {
      switch ($criterion) {
        case Rule::TAG_VALUE:
        case Rule::TAG_REGEX:
          $usage_count = (new GetUsageCountByRuleMatch($rule))($scope_object);
          if (empty($usage_count)) {
            return FALSE;
          }
          break;

        case Rule::ENTITY:
          if (!(new ValidateCriterion())($entity->getEntityTypeId(), $condition)) {
            return FALSE;
          }
          break;

        case Rule::BUNDLE:
          if (!(new ValidateCriterion())($entity->bundle(), $condition)) {
            return FALSE;
          }
          break;

        case Rule::HAS_FIELD:
          if (!$field_item_list || !(new ValidateCriterion())($field_item_list->getName(), $condition)) {
            return FALSE;
          }
          break;
      }
    }

    // If we got this far, all existing conditions have passed.
    return TRUE;
  }

}

<?php

namespace Drupal\field_tag\Rule;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\field_tag\FieldTagService;
use Drupal\field_tag\Helpers\ExplodeScopeObject;
use Drupal\field_tag\Helpers\FormatUsageValue;
use Drupal\field_tag\Plugin\Validation\Constraint\FieldTagConstraint;
use Drupal\field_tag\Service\LabelService;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CheckRequirements {

  /**
   * @var \Drupal\field_tag\FieldTagService
   */
  private $fieldTagService;

  /**
   * @var \Drupal\field_tag\Service\LabelService
   */
  private $labelService;

  /**
   * @var \Symfony\Component\Validator\Context\ExecutionContextInterface
   */
  private $context;

  /**
   * Tracks violations to prevent duplicates of the the same criteria.
   *
   * @var array
   */
  private $violationsAdded = [];

  /**
   * @var \Drupal\field_tag\Rule\Rule
   */
  private $rule;

  /**
   * @var array
   */
  private $requirements;

  /**
   * @var \Drupal\field_tag\Plugin\Validation\Constraint\FieldTagConstraint
   */
  private $constraint;

  /**
   * @param \Drupal\field_tag\FieldTagService $field_tag_service
   * @param \Drupal\field_tag\Service\LabelService $label_service
   * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
   */
  public function __construct(FieldTagService $field_tag_service, LabelService $label_service, ExecutionContextInterface $context) {
    $this->fieldTagService = $field_tag_service;
    $this->labelService = $label_service;
    $this->context = $context;
  }

  /**
   * All violations are added to $this->context.
   *
   * @param \Drupal\field_tag\Rule\Rule $rule
   * @param object $scope_object
   *   One of EntityInterface, FieldItemListInterface, FieldItemInterface
   *
   * @return void
   *
   * @see \Drupal\field_tag\Service\LabelService
   */
  public function __invoke(Rule $rule, object $scope_object): void {
    $this->rule = $rule;
    list($entity, $field_item_list, $field_item) = (new ExplodeScopeObject())($scope_object);
    $this->requirements = $this->rule->jsonSerialize()[Rule::REQUIRE] ?? [];
    $this->constraint = new FieldTagConstraint();
    if ($field_item) {
      $this->applyItemRequirements($field_item);
    }
    if ($field_item_list) {
      $this->applyFieldRequirements($field_item_list);
    }
    $this->applyEntityRequirements($entity);
  }

  private function applyEntityRequirements(EntityInterface $entity) {
    $criteria = array_intersect_key($this->requirements, array_flip([
      Rule::ENTITY,
      Rule::BUNDLE,
      Rule::TAG_MIN_PER_ENTITY,
      Rule::TAG_MAX_PER_ENTITY,
    ]));
    if (empty($criteria)) {
      return;
    }
    $labels = $this->labelService->scope($entity);

    $usage_count = (new GetUsageCountByRuleMatch($this->rule))($entity);
    foreach ($criteria as $criterion_type => $criterion) {
      switch ($criterion_type) {
        case Rule::ENTITY:
          $needs_violation = $this->getNeedsViolation('bundle', $criterion_type, $entity->getEntityTypeId(), $criterion);
          if ($needs_violation) {
            $this->context->addViolation(
              $this->constraint->badScopeMessage,
              [
                '%tag' => (string) $this->rule->getTags(),
                '%scope' => $labels[FieldTagConstraint::SCOPE_ENTITY] ?? $entity->getEntityType()
                    ->getLabel(),
              ]
            );
          }
          break;

        case Rule::BUNDLE:
          $needs_violation = $this->getNeedsViolation('bundle', $criterion_type, $entity->bundle(), $criterion);
          if ($needs_violation) {
            $this->context->addViolation(
              $this->constraint->badScopeMessage,
              [
                '%tag' => (string) $this->rule->getTags(),
                '%scope' => $labels[FieldTagConstraint::SCOPE_BUNDLE] ?? $entity->bundle(),
              ]
            );
          }
          break;

        case Rule::TAG_MIN_PER_ENTITY:
          $needs_violation = $this->getNeedsViolation('bundle', $criterion_type, $usage_count, $criterion);
          if ($needs_violation) {
            $this->context->addViolation(
              $this->constraint->belowMinMessage,
              [
                '%tag' => (string) $this->rule->getTags(),
                ':actual' => (new FormatUsageValue())($usage_count),
                ':expected' => (new FormatUsageValue())($criterion['value']),
                '%scope' => $labels[FieldTagConstraint::SCOPE_BUNDLE] ?? $entity->bundle(),
              ]
            );
          }
          break;

        case Rule::TAG_MAX_PER_ENTITY:
          $needs_violation = $this->getNeedsViolation('bundle', $criterion_type, $usage_count, $criterion);
          if ($needs_violation) {
            $this->context->addViolation(
              $this->constraint->aboveMaxMessage,
              [
                '%tag' => (string) $this->rule->getTags(),
                ':actual' => (new FormatUsageValue())($usage_count),
                ':expected' => (new FormatUsageValue())($criterion['value']),
                '%scope' => $labels[FieldTagConstraint::SCOPE_BUNDLE] ?? $entity->bundle(),
              ]
            );
          }
          break;
      }
    }
  }

  private function applyFieldRequirements(FieldItemListInterface $field_item_list) {
    $criteria = array_intersect_key($this->requirements, array_flip([
      Rule::TAGGED_FIELD,
      Rule::TAG_MIN_PER_FIELD,
      Rule::TAG_MAX_PER_FIELD,
    ]));
    if (empty($criteria)) {
      return;
    }
    $labels = $this->labelService->scope($field_item_list);
    $usage_count = (new GetUsageCountByRuleMatch($this->rule))($field_item_list);
    $cid = $field_item_list->getName();

    foreach ($criteria as $criterion_type => $criterion) {
      switch ($criterion_type) {
        case Rule::TAGGED_FIELD:
          $needs_violation = $this->getNeedsViolation($cid, $criterion_type, $field_item_list->getName(), $criterion);
          if ($needs_violation) {
            $this->context->addViolation(
              $this->constraint->badScopeMessage,
              [
                '%tag' => (string) $this->rule->getTags(),
                '%scope' => $labels[FieldTagConstraint::SCOPE_FIELD] ?? $field_item_list->getName(),
              ]
            );
          }
          break;

        case Rule::TAG_MIN_PER_FIELD:
          $needs_violation = $this->getNeedsViolation($cid, $criterion_type, $usage_count, $criterion);
          if ($needs_violation) {
            $this->context->addViolation(
              $this->constraint->belowMinMessage,
              [
                '%tag' => (string) $this->rule->getTags(),
                ':actual' => (new FormatUsageValue())($usage_count),
                ':expected' => (new FormatUsageValue())($criterion['value']),
                '%scope' => $labels[FieldTagConstraint::SCOPE_FIELD] ?? $field_item_list->getName(),
              ]
            );
          }
          break;

        case Rule::TAG_MAX_PER_FIELD:
          $needs_violation = $this->getNeedsViolation($cid, $criterion_type, $usage_count, $criterion);
          if ($needs_violation) {
            $this->context->addViolation(
              $this->constraint->aboveMaxMessage,
              [
                '%tag' => (string) $this->rule->getTags(),
                ':actual' => (new FormatUsageValue())($usage_count),
                ':expected' => (new FormatUsageValue())($criterion['value']),
                '%scope' => $labels[FieldTagConstraint::SCOPE_FIELD] ?? $field_item_list->getName(),
              ]
            );
          }
          break;
      }
    }
  }

  private function applyItemRequirements(?FieldItemInterface $field_item) {
    $criteria = array_intersect_key($this->requirements, array_flip([
      Rule::TAG_MIN_PER_ITEM,
    ]));
    if (empty($criteria)) {
      return;
    }
    $labels = $this->labelService->scope($field_item);
    $usage_count = (new GetUsageCountByRuleMatch($this->rule))($field_item);
    $cid = $field_item->getParent()->getName() . ':' . $field_item->getName();
    foreach ($criteria as $criterion_type => $criterion) {
      if ($criterion_type !== Rule::TAG_MIN_PER_ITEM) {
        continue;
      }
      $needs_violation = $this->getNeedsViolation($cid, $criterion_type, $usage_count, $criterion);
      if ($needs_violation) {

        $scope_label = $labels[FieldTagConstraint::SCOPE_FIELD_ITEM] ?? NULL;
        if (empty($scope_label)) {
          $scope_label = sprintf('%s #%d', $field_item->getParent()
            ->getName(), $field_item->getName());
        }

        $this->context->addViolation(
          $this->constraint->belowMinMessage,
          [
            '%tag' => (string) $this->rule->getTags(),
            ':actual' => (new FormatUsageValue())($usage_count),
            ':expected' => (new FormatUsageValue())($criterion['value']),
            '%scope' => $scope_label,
          ]
        );
      }
    }
  }

  private function getNeedsViolation(string $cid, $criterion_type, $value, $criterion) {
    $history_id = "$criterion_type:$cid";
    if (isset($this->violationsAdded[$this->rule->getHash()][$history_id])) {
      // If here, a previous run already returned true for this criterion and
      // context, so do not add the same violation again.
      return FALSE;
    }

    // One time validation
    if ((new ValidateCriterion())($value, $criterion)) {
      return FALSE;
    }

    // Track if we have
    $this->violationsAdded[$this->rule->getHash()][$history_id] = TRUE;

    return TRUE;
  }

}

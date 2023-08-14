<?php

namespace Drupal\field_tag\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\field_tag\Plugin\Validation\Constraint\FieldTagConstraint;
use Drupal\field_tag\Rule\Rule;

class ValidationService {

  private static $rules;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;


  /**
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * Get an aggregate of all validation rules provided by modules.
   *
   * @return \Drupal\field_tag\Rule\Rule[]
   *   An indexed array of rules.
   *
   * @see \hook_field_tag_validation_rules()
   */
  public function getAllValidationRules(): array {
    if (!isset(static::$rules)) {
      static::$rules = $this->moduleHandler->invokeAll('field_tag_validation_rules');
      foreach (static::$rules as $rule) {
        static::checkThatARuleIsCompleteOrThrow($rule);
      }
      // Make sure we have an indexed array.
      static::$rules = array_values(static::$rules);
    }

    return static::$rules;
  }

  /**
   * Test a rule to make sure it's valid.
   *
   * @param \Drupal\field_tag\Rule\Rule $rule
   *   The validation rule.
   *
   * @return void
   *
   * @throws \InvalidArgumentException If the rule is invalid.
   *
   * @see \hook_field_tag_validation_rules()
   */
  public static function checkThatARuleIsCompleteOrThrow(Rule $rule): void {
    $rule_data = $rule->jsonSerialize();

    // A rule must have at least one condition
    if (count($rule_data[Rule::CONDITION] ?? []) < 1) {
      throw new \InvalidArgumentException('Every rule must have at least one condition');
    }

    if (isset($rule_data[Rule::CONDITION][Rule::HAS_FIELD]) && isset($rule_data[Rule::REQUIRE][Rule::TAGGED_FIELD])) {
      throw new \InvalidArgumentException(sprintf('You cannot specific field(s) in %s and %s in one rule. %s', Rule::CONDITION, Rule::REQUIRE, json_encode($rule_data)));
    }

    // A rule must have a tag in the condition or requirement.
    if ($rule->getTags()->count() === 0) {
      throw new \InvalidArgumentException(sprintf('Every rule must specify a tag condition or requirement; add one of the keys: %s', implode(', ', [
        Rule::TAG_VALUE,
        Rule::TAG_REGEX,
      ])));
    }
  }
}

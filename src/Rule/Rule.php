<?php

namespace Drupal\field_tag\Rule;

use Drupal\field_tag\Tags;

/**
 * Represents a Field Tag Rule to be used in validation.
 *
 * @see \Drupal\field_tag\Plugin\Validation\Constraint\FieldTagConstraintValidator
 */
class Rule implements \JsonSerializable {

  const WAS_VIOLATED = 1;

  const TAG_VALUE = 'tag_value';

  const TAG_REGEX = 'tag_regex';

  const ENTITY = 'entity';

  const CALLABLE = 'callable';

  const BUNDLE = 'bundle';

  const HAS_FIELD = 'field_name';

  const TAGGED_FIELD = 'field_name';

  const CONDITION = 'conditions';

  const REQUIRE = 'require';

  const TAG_MIN_PER_ENTITY = 'min_per_entity';

  const TAG_MIN_PER_ITEM = 'min_per_item';

  const TAG_MIN_PER_FIELD = 'min_per_field';

  const TAG_MAX_PER_ENTITY = 'max_per_entity';

  const TAG_MAX_PER_ITEM = 'max_per_item';

  const TAG_MAX_PER_FIELD = 'max_per_field';

  const IN = 'IN';

  private $conditions = [];

  private $requirements = [];


  /**
   * Add a condition for when to apply the rule.
   *
   * @param string $criterion
   * @param $value
   * @param string $operator
   *   One of "=", "IN".  Case insensitive.
   *
   * @return $this
   */
  public function condition(string $criterion, $value, string $operator = '='): self {
    $this->validateNotEmpty([
      self::TAG_VALUE,
      self::TAG_REGEX,
      self::ENTITY,
      self::BUNDLE,
      self::HAS_FIELD,
    ], $criterion, $value);
    $this->validateOperator($value, $operator);
    $this->validateCriterionNotUsed($this->conditions, $criterion);
    $this->validateTagValueAndMatch($this->conditions, $criterion);
    $valid_criteria = [
      self::TAG_VALUE,
      self::TAG_REGEX,
      self::ENTITY,
      self::BUNDLE,
      self::HAS_FIELD,
      self::CALLABLE,
    ];
    $this->validateCriterion($valid_criteria, $criterion);
    $this->conditions[$criterion] = [$criterion, $value, $operator];

    return $this;
  }

  private function validateTagValueAndMatch(array $stack, string $criterion) {
    $xor = [self::TAG_VALUE, self::TAG_REGEX];
    $used = array_intersect_key($stack, array_flip($xor));
    if ($used && in_array($criterion, $xor) && !isset($stack[$criterion])) {
      throw new \InvalidArgumentException(sprintf('You cannot combine (%s) in a single rule.', implode(', ', $xor)));
    }
  }

  private function validateNotEmpty(array $filter, string $criterion, $value) {
    if (in_array($criterion, $filter) && empty($value)) {
      throw new \InvalidArgumentException(sprintf('The value for "%s" cannot be empty', $criterion));
    }
  }

  /**
   * @param $value
   * @param string $operator
   */
  private function validateOperator($value, string $operator) {
    if (!preg_match('/(=|in)/i', $operator)) {
      throw new \InvalidArgumentException(sprintf('Unsupported $operator: %s', $operator));
    }
    if (is_array($value) && !preg_match('/in/i', $operator)) {
      throw new \InvalidArgumentException('Array values must use the "in" operator');
    }
  }

  private function validateCriterionNotUsed(array $stack, string $criterion) {
    if (isset($stack[$criterion])) {
      throw new \InvalidArgumentException(sprintf('"%s" has already been used; it can only be set once per rule instance.', $criterion));
    }
  }

  private function validateCriterion(array $valid_set, string $criterion) {
    if (!in_array($criterion, $valid_set)) {
      throw new \InvalidArgumentException(sprintf('Invalid criterion "%s". These are allowed: %s', $criterion, implode(', ', $valid_set)));
    }
  }

  /**
   * Add a requirement for when a tag is matched.
   *
   * @param string $criterion
   * @param $value
   * @param string $operator
   *   One of "=", "IN".  Case insensitive.
   *
   * @return $this
   */
  public function require($criterion, $value = NULL, $operator = '='): self {
    $this->validateNotEmpty([
      self::TAG_VALUE,
      self::TAG_REGEX,
      self::ENTITY,
      self::BUNDLE,
      self::TAGGED_FIELD,
      self::TAG_MIN_PER_ENTITY,
      self::TAG_MIN_PER_FIELD,
      self::TAG_MIN_PER_ITEM,
    ], $criterion, $value);
    $this->validateOperator($value, $operator);
    $this->validateCriterionNotUsed($this->requirements, $criterion);
    $this->validateTagValueAndMatch($this->requirements, $criterion);
    $valid_criteria = [
      self::TAG_VALUE,
      self::TAG_REGEX,
      self::ENTITY,
      self::BUNDLE,
      self::TAGGED_FIELD,
      self::TAG_MIN_PER_ENTITY,
      self::TAG_MIN_PER_FIELD,
      self::TAG_MIN_PER_ITEM,
      self::TAG_MAX_PER_ENTITY,
      self::TAG_MAX_PER_FIELD,
      self::TAG_MAX_PER_ITEM,
    ];
    $this->validateCriterion($valid_criteria, $criterion);
    $this->validateNumericValuesAsAppropriate($criterion, $value);
    $this->requirements[$criterion] = [$criterion, $value, $operator];

    return $this;
  }

  /**
   * Get all referenced tag values or regexes in this rule.
   *
   * @return \Drupal\field_tag\Tags
   *   All tags that are included in this rule, values, regex and both
   *   conditions and requirements.
   */
  public function getTags(): Tags {
    $data = $this->jsonSerialize();
    $tags = new Tags();
    foreach ([self::CONDITION, self::REQUIRE] as $type) {
      foreach ([
                 self::TAG_VALUE,
                 self::TAG_REGEX,
               ] as $criterion) {
        $value = $data[$type][$criterion]['value'] ?? [];
        if ($value) {
          $tags = $tags->merge(new Tags(...$value));
        }
      }
    }

    return $tags->sort();
  }

  /**
   * @return array
   *   An a unique array of all field names referenced in this rule.
   */
  public function getRequiredFieldNames(): array {
    $data = $this->jsonSerialize();

    return $data[self::REQUIRE][self::TAGGED_FIELD]['value'] ?? [];
  }

  private function normalizeValue($criterion, $value, $operator) {

    // These are the integer criteria.
    if (in_array($criterion, [
      self::TAG_MIN_PER_ENTITY,
      self::TAG_MIN_PER_FIELD,
      self::TAG_MIN_PER_ITEM,
    ])) {
      return ['value' => (int) $value, 'operator' => '>='];
    }
    if (in_array($criterion, [
      self::TAG_MAX_PER_ENTITY,
      self::TAG_MAX_PER_FIELD,
      self::TAG_MAX_PER_ITEM,
    ])) {
      return ['value' => (int) $value, 'operator' => '<='];
    }

    if ('=' === $operator) {
      $value = [$value];
      $operator = self::IN;
    }

    return [
      'value' => $value,
      'operator' => strtoupper($operator),
    ];
  }

  public function jsonSerialize() {
    $data = [];
    ksort($this->conditions);
    ksort($this->requirements);
    foreach ($this->conditions as $condition) {
      list($criterion, $value, $operator) = $condition;
      $data[self::CONDITION][$criterion] = $this->normalizeValue($criterion, $value, $operator);
    }
    foreach ($this->requirements as $requirement) {
      list($criterion, $value, $operator) = $requirement;
      $data[self::REQUIRE][$criterion] = $this->normalizeValue($criterion, $value, $operator);
    }

    return $data;
  }


  /**
   * Get a content hash.
   *
   * @return string
   *   A hash of the content.  This will be the same for two instances with the
   *   same condition(s) and requirement(s).
   */
  public function getHash(): string {
    return md5(json_encode($this));
  }

  private function validateNumericValuesAsAppropriate($criterion, $value) {
    $numeric_only_value_criteria = [
      Rule::TAG_MIN_PER_ENTITY,
      Rule::TAG_MIN_PER_FIELD,
      Rule::TAG_MIN_PER_ITEM,
      Rule::TAG_MAX_PER_ENTITY,
      Rule::TAG_MAX_PER_FIELD,
      Rule::TAG_MAX_PER_ITEM,
    ];
    if (in_array($criterion, $numeric_only_value_criteria) && !is_numeric($value)) {
      throw new \InvalidArgumentException(sprintf('Criterion "%s" only allows numeric values.  "%s" is invalid.', $criterion, $value));
    }
  }
}

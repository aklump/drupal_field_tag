<?php

namespace Drupal\field_tag\Rule;

class ValidateCriterion {

  public function __invoke($value, $criterion) {
    switch ($criterion['operator'] ?? NULL) {
      case '>=':
        return $value >= $criterion['value'];

      case '<=':
        return $value <= $criterion['value'];

      case Rule::IN:
        return in_array($value, $criterion['value'] ?? []);
    }

    return TRUE;
  }
}

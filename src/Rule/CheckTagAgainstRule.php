<?php

namespace Drupal\field_tag\Rule;

class CheckTagAgainstRule {

  public function __construct(Rule $rule) {
    $this->rule = $rule;
  }

  public function __invoke(string $some_tag_value) {
    $data = $this->rule->jsonSerialize();
    foreach ([Rule::CONDITION, Rule::REQUIRE] as $type) {
      if (isset($data[$type][Rule::TAG_VALUE]['value'])
        && in_array($some_tag_value, $data[$type][Rule::TAG_VALUE]['value'])) {
        return TRUE;
      }
      if (isset($data[$type][Rule::TAG_REGEX]['value'])) {
        foreach (($data[$type][Rule::TAG_REGEX]['value'] ?? []) as $regex) {
          if (preg_match($regex, $some_tag_value)) {
            return TRUE;
          }
        }
      }
    }
  }

}

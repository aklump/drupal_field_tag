<?php

namespace Drupal\field_tag\Helpers;

class FormatUsageValue {

  public function __invoke(int $usage_count) {
    if ($usage_count > 1) {
      return "$usage_count times";
    }
    if ($usage_count === 1) {
      return 'once';
    }
    if ($usage_count === 0) {
      return 'not once';
    }
  }

}

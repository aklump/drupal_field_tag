<?php

namespace Drupal\Tests\field_tag\Integration;

use Drupal\field_tag\Plugin\Validation\Constraint\FieldTagConstraint;
use Drupal\field_tag\Rule\Rule;
use Drupal\field_tag\Service\ValidationService;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Drupal\field_tag\Service\ValidationService
 */
class ValidationServiceTest extends TestCase {

  /**
   * Provides data for testValidateRuleThrowsAsExpected.
   */
  public function dataForTestValidateRuleThrowsAsExpectedProvider() {
    $rules = [];
    $rules[] = (new Rule())->require(Rule::TAG_VALUE, 'foo');
    $rules[] = new Rule();
    $rules[] = (new Rule())
      ->condition(Rule::BUNDLE, 'page')
      ->require(Rule::TAG_MIN_PER_FIELD, 1);
    $rules[] = (new Rule())
      ->condition(Rule::TAG_VALUE, 'lorem')
      ->condition(Rule::HAS_FIELD, 'field_images')
      ->require(Rule::TAGGED_FIELD, 'field_images');

    return array_map(function (Rule $rule) {
      return [$rule];
    }, $rules);
  }

  /**
   * @dataProvider dataForTestValidateRuleThrowsAsExpectedProvider
   */
  public function testValidateRuleThrowsAsExpected($rule) {
    $this->expectException(\InvalidArgumentException::class);
    ValidationService::checkThatARuleIsCompleteOrThrow($rule);
  }

}

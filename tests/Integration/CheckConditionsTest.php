<?php

namespace Drupal\Tests\field_tag\Integration;

use Drupal\field_tag\Rule\CheckConditions;
use Drupal\field_tag\Rule\Rule;
use Drupal\node\NodeInterface;
use Drupal\Tests\field_tag\Integration\TestTraits\RuleTestTrait;
use PHPUnit\Framework\TestCase;

final class CheckConditionsTest extends TestCase {

  use RuleTestTrait;

  public function dataFortestLastConditionFailsReturnsFalseProvider() {
    $tests = [];
    $tests[] = [
      (new Rule())
        ->condition(Rule::CALLABLE, fn() => TRUE)
        ->condition(Rule::ENTITY, 'node')
        ->condition(Rule::BUNDLE, 'page')
        ->condition(Rule::HAS_FIELD, 'field_foo')
        ->condition(Rule::TAG_VALUE, 'goo'),
    ];
    $tests[] = [
      (new Rule())
        ->condition(Rule::TAG_VALUE, 'foo')
        ->condition(Rule::CALLABLE, fn() => TRUE)
        ->condition(Rule::ENTITY, 'node')
        ->condition(Rule::BUNDLE, 'page')
        ->condition(Rule::HAS_FIELD, 'field_bar'),
    ];
    $tests[] = [
      (new Rule())
        ->condition(Rule::HAS_FIELD, 'field_foo')
        ->condition(Rule::TAG_VALUE, 'foo')
        ->condition(Rule::CALLABLE, fn() => TRUE)
        ->condition(Rule::ENTITY, 'node')
        ->condition(Rule::BUNDLE, 'blog'),
    ];
    $tests[] = [
      (new Rule())
        ->condition(Rule::BUNDLE, 'page')
        ->condition(Rule::HAS_FIELD, 'field_foo')
        ->condition(Rule::TAG_VALUE, 'foo')
        ->condition(Rule::CALLABLE, fn() => TRUE)
        ->condition(Rule::ENTITY, 'user'),
    ];
    $tests[] = [
      (new Rule())
        ->condition(Rule::ENTITY, 'node')
        ->condition(Rule::BUNDLE, 'page')
        ->condition(Rule::HAS_FIELD, 'field_foo')
        ->condition(Rule::TAG_VALUE, 'foo')
        ->condition(Rule::CALLABLE, fn() => FALSE),
    ];

    return $tests;
  }

  /**
   * @dataProvider dataFortestLastConditionFailsReturnsFalseProvider
   */
  public function testLastConditionFailsReturnsFalse(Rule $rule) {
    $node = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_foo', 'foo')
      ->mockDrupalEntity();

    $result = (new CheckConditions())($rule, $node->field_foo);
    $this->assertFalse($result);
  }

  public function testCallablePassesEntityFailsReturnsFalse() {
    $rule = (new Rule())
      ->condition(Rule::CALLABLE, fn() => TRUE)
      ->condition(Rule::ENTITY, 'user');

    $scope_object = $this
      ->createDrupalEntity('node', 'blog_entry')
      ->mockDrupalEntity();

    $result = (new CheckConditions())($rule, $scope_object);
    $this->assertFalse($result);
  }

  public function testCallablePassesEntityPassedReturnsTrue() {
    $rule = (new Rule())
      ->condition(Rule::CALLABLE, fn() => TRUE)
      ->condition(Rule::ENTITY, 'node');

    $scope_object = $this
      ->createDrupalEntity('node', 'blog_entry')
      ->mockDrupalEntity();

    $result = (new CheckConditions())($rule, $scope_object);
    $this->assertTrue($result);
  }

  public function testRuleWithNoConditionsReturnsTrue() {
    $rule = (new Rule());
    $scope_object = $this->createMock(NodeInterface::class);
    $result = (new CheckConditions())($rule, $scope_object);
    $this->assertTrue($result);
  }
}

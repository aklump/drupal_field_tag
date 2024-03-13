<?php

namespace Drupal\Tests\field_tag\Unit;

use Drupal\field_tag\Rule\Rule;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Drupal\field_tag\Rule
 */
class RuleTest extends TestCase {

  public function dataFortestNonNumericThrowsForCertainConditionsProvider() {
    $tests = [];
    $tests[] = [
      Rule::TAG_MIN_PER_ENTITY,
    ];
    $tests[] = [
      Rule::TAG_MIN_PER_FIELD,
    ];
    $tests[] = [
      Rule::TAG_MIN_PER_ITEM,
    ];
    $tests[] = [
      Rule::TAG_MAX_PER_ENTITY,
    ];
    $tests[] = [
      Rule::TAG_MAX_PER_FIELD,
    ];
    $tests[] = [
      Rule::TAG_MAX_PER_ITEM,
    ];

    return $tests;
  }

  /**
   * @dataProvider dataFortestNonNumericThrowsForCertainConditionsProvider
   */
  public function testNonNumericThrowsForCertainConditions(string $numeric_value_criterion) {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('#' . $numeric_value_criterion . '.+foo#');
    (new Rule())->require($numeric_value_criterion, 'foo');
  }

  public function testGetTagReturnsExpected() {
    $tags = (new Rule())->condition(Rule::TAG_VALUE, 'alpha')->getTags();
    $this->assertSame('alpha', (string) $tags);

    $tags = (new Rule())->require(Rule::TAG_VALUE, 'bravo')->getTags();
    $this->assertSame('bravo', (string) $tags);

    $tags = (new Rule())->condition(Rule::TAG_REGEX, '/^lang--.+$/')->getTags();
    $this->assertSame('/^lang--.+$/', (string) $tags);

    $tags = (new Rule())->require(Rule::TAG_REGEX, '/^foo\d+$/')->getTags();
    $this->assertSame('/^foo\d+$/', (string) $tags);

    $tags = (new Rule())->condition(Rule::TAG_VALUE, 'alpha')->getTags();
    $this->assertSame('alpha', (string) $tags);

    $tags = (new Rule())
      ->condition(Rule::TAG_VALUE, 'alpha')
      ->require(Rule::TAG_VALUE, 'bravo')
      ->getTags();
    $this->assertSame('alpha,bravo', (string) $tags);
    $tags = (new Rule())
      ->condition(Rule::TAG_REGEX, '/alpha/')
      ->require(Rule::TAG_REGEX, '/bravo/')
      ->getTags();
    $this->assertSame('/alpha/,/bravo/', (string) $tags);
    $tags = (new Rule())
      ->condition(Rule::TAG_VALUE, 'alpha')
      ->require(Rule::TAG_REGEX, '/^bravo\d+/')
      ->getTags();
    $this->assertSame('/^bravo\d+/,alpha', (string) $tags);
    $tags = (new Rule())
      ->condition(Rule::TAG_REGEX, '/^bravo\d+/')
      ->require(Rule::TAG_VALUE, 'alpha')
      ->getTags();
    $this->assertSame('/^bravo\d+/,alpha', (string) $tags);
  }

  public function testGetChecksumReturnsSameHashForSameContentOnTwoInstances() {
    $rule1 = (new Rule())
      ->condition(Rule::ENTITY, 'node')
      ->condition(Rule::TAG_VALUE, 'lorem');
    $rule2 = (new Rule())
      ->condition(Rule::TAG_VALUE, 'lorem')
      ->condition(Rule::ENTITY, 'node');
    $this->assertSame($rule1->getHash(), $rule2->getHash());
  }

  public function testGetHasReturnsSameStringCalledTwice() {
    $rule = (new Rule())->condition(Rule::TAG_VALUE, 'lorem');
    $id = $rule->getHash();
    $this->assertIsString($id);
    $this->assertSame($id, $rule->getHash());
  }

  public function testGetRequiredFieldNamesReturnsExpectedArray() {
    $field_names = (new Rule())
      ->condition(Rule::HAS_FIELD, 'field_alpha')
      ->require(Rule::TAGGED_FIELD, 'field_bravo')
      ->getRequiredFieldNames();
    $this->assertCount(1, $field_names);
    $this->assertNotContains('field_alpha', $field_names);
    $this->assertContains('field_bravo', $field_names);

    $field_names = (new Rule())
      ->condition(Rule::HAS_FIELD, ['field_bravo', 'field_charlie'], 'in')
      ->require(Rule::TAGGED_FIELD, ['field_echo', 'field_foxtrot'], 'in')
      ->getRequiredFieldNames();
    $this->assertCount(2, $field_names);
    $this->assertNotContains('field_bravo', $field_names);
    $this->assertNotContains('field_charlie', $field_names);
    $this->assertContains('field_echo', $field_names);
    $this->assertContains('field_foxtrot', $field_names);
  }

  public function testJsonSerializeReturnsTagMatchAsExpected() {
    $data = (new Rule())
      ->condition(Rule::TAG_REGEX, '/^english|spanish$/')
      ->jsonSerialize();
    $this->assertArrayHasKey(Rule::CONDITION, $data);
    foreach ([
               Rule::TAG_REGEX,
             ] as $criterion) {
      $this->assertArrayHasKey($criterion, $data[Rule::CONDITION]);
      $this->assertArrayHasKey('value', $data[Rule::CONDITION][$criterion]);
      $this->assertArrayHasKey('operator', $data[Rule::CONDITION][$criterion]);
      $this->assertIsArray($data[Rule::CONDITION][$criterion]['value']);
      $this->assertIsString($data[Rule::CONDITION][$criterion]['operator']);

      $this->assertSame('/^english|spanish$/', $data[Rule::CONDITION][$criterion]['value'][0]);
    }
  }

  public function testJsonSerializeReturnsAllCriterionsAsExpected() {
    $data = (new Rule())
      ->condition(Rule::TAG_VALUE, 'sidebar')
      ->condition(Rule::ENTITY, 'node')
      ->condition(Rule::BUNDLE, ['lesson_plan', 'film'], 'in')
      ->condition(Rule::HAS_FIELD, 'field_main')
      ->require(Rule::TAG_VALUE, 'sidebar')
      ->require(Rule::ENTITY, 'node')
      ->require(Rule::BUNDLE, ['lesson_plan', 'film'], 'in')
      ->require(Rule::TAGGED_FIELD, 'field_main')
      ->require(Rule::TAG_MIN_PER_ENTITY, 1)
      ->require(Rule::TAG_MAX_PER_ENTITY, 1)
      ->require(Rule::TAG_MIN_PER_FIELD, 1)
      ->require(Rule::TAG_MAX_PER_FIELD, 1)
      ->require(Rule::TAG_MIN_PER_ITEM, 1)
      ->jsonSerialize();

    $this->assertArrayHasKey(Rule::CONDITION, $data);
    foreach ([
               Rule::TAG_VALUE,
               Rule::ENTITY,
               Rule::BUNDLE,
               Rule::HAS_FIELD,
             ] as $criterion) {
      $this->assertArrayHasKey($criterion, $data[Rule::CONDITION]);
      $this->assertArrayHasKey('value', $data[Rule::CONDITION][$criterion]);
      $this->assertArrayHasKey('operator', $data[Rule::CONDITION][$criterion]);
      $this->assertIsArray($data[Rule::CONDITION][$criterion]['value']);
      $this->assertIsString($data[Rule::CONDITION][$criterion]['operator']);
    }

    $this->assertArrayHasKey(Rule::REQUIRE, $data);
    foreach ([
               Rule::TAG_VALUE,
               Rule::ENTITY,
               Rule::BUNDLE,
               Rule::TAGGED_FIELD,
             ] as $criterion) {
      $this->assertArrayHasKey($criterion, $data[Rule::REQUIRE]);
      $this->assertArrayHasKey('value', $data[Rule::REQUIRE][$criterion]);
      $this->assertArrayHasKey('operator', $data[Rule::REQUIRE][$criterion]);
      $this->assertIsArray($data[Rule::REQUIRE][$criterion]['value']);
      $this->assertSame(Rule::IN, $data[Rule::REQUIRE][$criterion]['operator']);
    }
    foreach ([
               Rule::TAG_MIN_PER_ENTITY,
               Rule::TAG_MIN_PER_FIELD,
               Rule::TAG_MIN_PER_ITEM,
             ] as $criterion) {
      $this->assertArrayHasKey('value', $data[Rule::REQUIRE][$criterion]);
      $this->assertArrayHasKey('operator', $data[Rule::REQUIRE][$criterion]);
      $this->assertSame('>=', $data[Rule::REQUIRE][$criterion]['operator']);
    }
    foreach ([
               Rule::TAG_MAX_PER_ENTITY,
               Rule::TAG_MAX_PER_FIELD,
             ] as $criterion) {
      $this->assertArrayHasKey('value', $data[Rule::REQUIRE][$criterion]);
      $this->assertArrayHasKey('operator', $data[Rule::REQUIRE][$criterion]);
      $this->assertSame('<=', $data[Rule::REQUIRE][$criterion]['operator']);
    }
  }

  public function testJsonSerializeReturnsArray() {
    $data = (new Rule())
      ->condition(Rule::TAG_VALUE, 'foo')
      ->condition(Rule::ENTITY, 'node')
      ->require(Rule::TAG_MIN_PER_ENTITY, 1)
      ->jsonSerialize();
    $this->assertIsArray($data);
  }

  public function testRequireWithRepeatedCriterionThrows() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('/once per rule/');
    (new Rule())
      ->require(Rule::BUNDLE, 'foo')
      ->require(Rule::BUNDLE, 'foo');
  }

  public function testRequireWithTagValueAndTagMatchThrows() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('/combine/');
    (new Rule())
      ->require(Rule::TAG_VALUE, 'foo')
      ->require(Rule::TAG_REGEX, '^foo\d+$');
  }

  public function testRequireWithValidStringValuesReturnsSelf() {
    $rule = new Rule();
    $result = $rule
      ->require(Rule::ENTITY, 'node')
      ->require(Rule::BUNDLE, 'page')
      ->require(Rule::TAGGED_FIELD, 'field_main')
      ->require(Rule::TAG_VALUE, 'foo');
    $this->assertSame($result, $rule);

    $rule = new Rule();
    $result = $rule
      ->require(Rule::ENTITY, 'node')
      ->require(Rule::BUNDLE, 'page')
      ->require(Rule::TAGGED_FIELD, 'field_main')
      ->require(Rule::TAG_REGEX, 'foo');
    $this->assertSame($result, $rule);
  }

  public function testRequireWithArrayValueAndEqualOperatorThrows() {
    $this->expectException(\InvalidArgumentException::class);
    (new Rule())->require(Rule::BUNDLE, ['foo']);
  }

  public function testRequireWithValidArrayValuesReturnsSelf() {
    $rule = new Rule();
    $result = $rule
      ->require(Rule::ENTITY, ['node', 'user'], 'IN')
      ->require(Rule::BUNDLE, ['page', 'blog'], 'IN')
      ->require(Rule::TAGGED_FIELD, ['field_main', 'field_secondary'], 'IN')
      ->require(Rule::TAG_VALUE, ['foo', 'bar'], 'IN');
    $this->assertSame($result, $rule);
  }

  public function testRequireWithItemMaxCriterionThrows() {
    $this->expectException(\InvalidArgumentException::class);
    (new Rule())->require('item_max', 1);
  }

  public function testRequireWithValidMinMaxValuesReturnsSelf() {
    $rule = new Rule();
    $result = $rule
      ->require(Rule::TAG_MIN_PER_ENTITY, 1)
      ->require(Rule::TAG_MAX_PER_ENTITY, 1)
      ->require(Rule::TAG_MIN_PER_FIELD, 1)
      ->require(Rule::TAG_MAX_PER_FIELD, 1)
      ->require(Rule::TAG_MIN_PER_ITEM, 1);
    $this->assertSame($result, $rule);
  }

  public function testConditionWithRepeatedCriterionThrows() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('/once per rule/');
    (new Rule())
      ->condition(Rule::BUNDLE, 'foo')
      ->condition(Rule::BUNDLE, 'foo');
  }

  public function testConditionWithTagValueAndTagMatchThrows() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('/combine/');
    (new Rule())
      ->condition(Rule::TAG_VALUE, 'foo')
      ->condition(Rule::TAG_REGEX, '^foo\d+$');
  }

  public function testConditionWithValidStringValuesReturnsSelf() {
    $rule = new Rule();
    $result = $rule
      ->condition(Rule::ENTITY, 'node')
      ->condition(Rule::BUNDLE, 'page')
      ->condition(Rule::HAS_FIELD, 'field_main')
      ->condition(Rule::TAG_VALUE, 'foo');
    $this->assertSame($result, $rule);

    $rule = new Rule();
    $result = $rule
      ->condition(Rule::ENTITY, 'node')
      ->condition(Rule::BUNDLE, 'page')
      ->condition(Rule::HAS_FIELD, 'field_main')
      ->condition(Rule::TAG_REGEX, 'foo');
    $this->assertSame($result, $rule);
  }

  public function testConditionWithArrayValueAndEqualOperatorThrows() {
    $this->expectException(\InvalidArgumentException::class);
    (new Rule())->condition(Rule::BUNDLE, ['foo']);
  }

  /**
   * Provides data for testEmptyConditionThrows.
   */
  public function dataForTestEmptyConditionThrowsProvider() {
    $tests = [];
    $tests[] = [Rule::TAG_VALUE];
    $tests[] = [Rule::TAG_REGEX];
    $tests[] = [Rule::ENTITY];
    $tests[] = [Rule::BUNDLE];
    $tests[] = [Rule::HAS_FIELD];
    $tests[] = [Rule::TAG_MIN_PER_ENTITY];
    $tests[] = [Rule::TAG_MIN_PER_FIELD];
    $tests[] = [Rule::TAG_MIN_PER_ITEM];

    return $tests;
  }

  /**
   * @dataProvider dataForTestEmptyConditionThrowsProvider
   */
  public function testEmptyRequireThrows($criterion) {
    $this->expectException(\InvalidArgumentException::class);
    (new Rule())->require($criterion, '');
  }

  /**
   * @dataProvider dataForTestEmptyConditionThrowsProvider
   */
  public function testEmptyConditionThrows($criterion) {
    $this->expectException(\InvalidArgumentException::class);
    (new Rule())->condition($criterion, '');
  }

  public function testConditionWithValidArrayValuesReturnsSelf() {
    $rule = new Rule();
    $result = $rule
      ->condition(Rule::ENTITY, ['node', 'user'], 'IN')
      ->condition(Rule::BUNDLE, ['page', 'blog'], 'IN')
      ->condition(Rule::HAS_FIELD, ['field_main', 'field_secondary'], 'IN')
      ->condition(Rule::TAG_VALUE, ['foo', 'bar'], 'IN');
    $this->assertSame($result, $rule);
  }

}

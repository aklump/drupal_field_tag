<?php

namespace Drupal\Tests\field_tag\Integration;

use AKlump\Drupal\PHPUnit\Integration\Framework\MockObject\MockDrupalEntityTrait;
use Drupal\field_tag\Rule\GetUsageCountByRuleMatch;
use Drupal\field_tag\Rule\Rule;
use Drupal\Tests\field_tag\Integration\TestTraits\RuleTestTrait;
use PHPUnit\Framework\TestCase;

class GetUsageCountByRuleMatchTest extends TestCase {

  use RuleTestTrait;
  use MockDrupalEntityTrait;

  public function dataEntityProvider() {
    $tests = [];

    $tests[] = [
      0,
      $this->createFieldItemListMock([]),
    ];

    $tests[] = [
      8,
      $this->createFieldItemListMock([
        ['field_tag' => 'alpha'],
        ['field_tag' => 'bravo'],
        ['field_tag' => 'bravo'],
        ['field_tag' => 'bravo'],
        ['field_tag' => 'bravo'],
        ['field_tag' => 'bravo'],
        ['field_tag' => 'bravo'],
        ['field_tag' => 'bravo'],
      ]),
    ];
    $tests[] = [
      13,
      $this->createFieldItemListMock([
        ['field_tag' => 'alpha'],
        ['field_tag' => 'alpha'],
        ['field_tag' => 'alpha'],
        ['field_tag' => 'bravo'],
        ['field_tag' => 'bravo'],
        ['field_tag' => 'bravo'],
        ['field_tag' => 'bravo'],
        ['field_tag' => 'bravo'],
        ['field_tag' => 'bravo'],
        ['field_tag' => 'bravo'],
        ['field_tag' => 'bravo'],
        ['field_tag' => 'bravo'],
        ['field_tag' => 'bravo'],
      ]),
    ];

    return $tests;
  }

  /**
   * @dataProvider dataEntityProvider
   */
  public function testInvokeReturnsExpectedCountForRequire(int $expected_count, object $countable_object) {
    $rule = (new Rule())
      ->require(Rule::TAG_REGEX, '/^alpha|bravo$/');
    $this->assertSame($expected_count, (new GetUsageCountByRuleMatch($rule))($countable_object));
  }

  /**
   * @dataProvider dataEntityProvider
   */
  public function testInvokeReturnsExpectedCountForCondition(int $expected_count, object $countable_object) {
    $rule = (new Rule())
      ->condition(Rule::TAG_REGEX, '/^alpha|bravo$/');
    $this->assertSame($expected_count, (new GetUsageCountByRuleMatch($rule))($countable_object));
  }

  /**
   * @dataProvider dataEntityProvider
   */
  public function testInvokeReturnsExpectedCountForConditionAndRequire(int $expected_count, object $countable_object) {
    $rule = (new Rule())
      ->require(Rule::TAG_REGEX, '/^alpha|bravo$/')
      ->condition(Rule::TAG_REGEX, '/^alpha|bravo$/');
    $this->assertSame($expected_count, (new GetUsageCountByRuleMatch($rule))($countable_object));
  }

  public function testConditionRegexCountsMultipleRuleFields() {
    $rule = (new Rule())
      ->condition(Rule::TAG_VALUE, 'foo')
      ->condition(Rule::HAS_FIELD, ['field_bravo', 'field_alpha'], 'IN');
    $entity = $this->createEntityMock('node', 'page', [
      'field_alpha' => [['field_tag' => 'foo']],
      'field_bravo' => [
        ['field_tag' => 'foo'],
        ['field_tag' => 'foo'],
      ],
    ]);
    $usage_count = (new GetUsageCountByRuleMatch($rule))($entity);
    $this->assertSame(3, $usage_count);
  }

  public function testConditionHasFieldDoesNotLimitCountsToItsValue() {
    $rule = (new Rule())
      ->condition(Rule::TAG_REGEX, '/^foo/')
      ->condition(Rule::HAS_FIELD, 'field_bravo');
    $entity = $this->createEntityMock('node', 'page', [
      'field_alpha' => [['field_tag' => 'foo']],
      'field_bravo' => [
        ['field_tag' => 'fool'],
        ['field_tag' => 'foolhardy'],
      ],
    ]);
    $usage_count = (new GetUsageCountByRuleMatch($rule))($entity);
    $this->assertSame(3, $usage_count);
  }

  public function testConditionRegexCountsAllFields() {
    $rule = (new Rule())
      ->condition(Rule::TAG_REGEX, '/^foo/');
    $entity = $this->createEntityMock('node', 'page', [
      'field_alpha' => [['field_tag' => 'foo']],
      'field_bravo' => [
        ['field_tag' => 'fool'],
        ['field_tag' => 'foolhardy'],
      ],
    ]);
    $usage_count = (new GetUsageCountByRuleMatch($rule))($entity);
    $this->assertSame(3, $usage_count);
  }

  public function testCompareNonRuleFieldTaggedCountsWhenNoRuleRequirement() {
    $rule = (new Rule())
      ->condition(Rule::ENTITY, ['node', 'user'], 'in')
      ->condition(Rule::HAS_FIELD, 'field_images')
      ->require(Rule::TAG_VALUE, 'teaser')
      ->require(Rule::TAG_MIN_PER_ENTITY, 1);
    $entity = $this->createEntityMock('user', 'user', [
      'field_images' => [],
      'field_other' => [
        ['field_tag' => 'teaser'],
      ],
    ]);
    $usage_count = (new GetUsageCountByRuleMatch($rule))($entity);
    $this->assertSame(1, $usage_count);
  }

  public function testConditionFooBarCountsBoth() {
    $rule = (new Rule())
      ->condition(Rule::TAG_VALUE, ['foo', 'bar'], 'in')
      ->require(Rule::TAG_MIN_PER_ENTITY, 1);
    $entity = $this->createEntityMock('node', 'page', [
      'field_alpha' => [['field_tag' => 'foo']],
      'field_bravo' => [['field_tag' => 'bar']],
    ]);
    $usage_count = (new GetUsageCountByRuleMatch($rule))($entity);
    $this->assertSame(2, $usage_count);
  }

  public function testConditionFooCountsOnlyFooNotBar() {
    $rule = (new Rule())
      ->condition(Rule::TAG_VALUE, 'foo')
      ->require(Rule::TAG_MIN_PER_ENTITY, 1);
    $entity = $this->createEntityMock('node', 'page', [
      'field_alpha' => [['field_tag' => 'foo']],
      'field_bravo' => [['field_tag' => 'bar']],
    ]);
    $usage_count = (new GetUsageCountByRuleMatch($rule))($entity);
    $this->assertSame(1, $usage_count);
  }

}

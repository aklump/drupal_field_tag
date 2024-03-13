<?php

namespace Drupal\Tests\field_tag\Integration;

use AKlump\Drupal\PHPUnit\Integration\Framework\MockObject\MockDrupalEntityTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\field_tag\Rule\Rule;
use PHPUnit\Framework\TestCase;

class RuleCallableTest extends TestCase {

  use RuleTestTrait;
  use MockDrupalEntityTrait;

  public function testNoReturnValueAssumesFalse() {
    $rule = (new Rule())
      ->condition(Rule::CALLABLE, function () {
        // Do not return anything to test the assumption.
      })
      ->require(Rule::TAG_VALUE, 'foo')
      ->require(Rule::TAG_MIN_PER_ENTITY, 1);
    $this->addRule($rule);
    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);
  }

  public function testAssertCallableReceivesExpectedArgumentsForNode() {
    $call_count = 0;
    $args = [];
    $rule = (new Rule())
      ->condition(Rule::CALLABLE, function () use (&$args, &$call_count) {
        ++$call_count;
        $args[] = func_get_args();
      });
    $this->addRule($rule);

    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->mockDrupalEntity();
    $this->validateDrupalEntity($entity);

    $args = array_shift($args);
    $this->assertSame(1, $call_count);
    $this->assertCount(2, $args);
    $this->assertInstanceOf(EntityInterface::class, $args[0]);
    $this->assertSame($entity, $args[0]);
    $this->assertNull($args[1]);
  }

  public function testAssertCallableReceivesExpectedArgumentsForFieldItem() {
    $call_count = 0;
    $args = [];
    $rule = (new Rule())
      ->condition(Rule::HAS_FIELD, 'field_foo')
      ->condition(Rule::CALLABLE, function () use (&$args, &$call_count) {
        ++$call_count;
        $args[] = func_get_args();
      });
    $this->addRule($rule);

    $entity = $this
      ->createDrupalEntity('node', 'page', ['field_foo'])
      ->mockDrupalEntity();
    $this->validateDrupalEntity($entity);

    $this->assertSame(2, $call_count);
    list($entity_and_list, $list) = $args;

    $this->assertCount(2, $entity_and_list);
    $this->assertInstanceOf(EntityInterface::class, $entity_and_list[0]);
    $this->assertSame($entity, $entity_and_list[0]);
    $this->assertInstanceOf(FieldItemListInterface::class, $entity_and_list[1]);
    $this->assertSame($entity->field_foo, $entity_and_list[1]);

    $this->assertCount(2, $list);
    $this->assertInstanceOf(EntityInterface::class, $list[0]);
    $this->assertSame($entity, $list[0]);
    $this->assertNull($list[1]);
  }

  public function testAssertCallableReceivesExpectedArgumentsForParagraph() {
    $call_count = 0;
    $args = [];
    $rule = (new Rule())->condition(Rule::CALLABLE, function () use (&$args, &$call_count) {
      ++$call_count;
      $args[] = func_get_args();
    });
    $this->addRule($rule);

    $entity = $this
      ->createDrupalEntity('paragraph', 'foo')
      ->mockDrupalEntity();
    $this->validateDrupalEntity($entity);

    $args = array_shift($args);
    $this->assertSame(1, $call_count);
    $this->assertCount(2, $args);
    $this->assertInstanceOf(EntityInterface::class, $args[0]);
    $this->assertSame($entity, $args[0]);
    $this->assertNull($args[1]);
  }

  public function testAssertCallableReturnsTrueAppliesRule() {
    $rule = (new Rule())->condition(Rule::CALLABLE, function () {
      return TRUE;
    })
      ->require(Rule::TAG_VALUE, 'foo')
      ->require(Rule::TAG_MIN_PER_ENTITY, 1);
    $this->addRule($rule);

    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(1, $violations);
  }

  public function testAssertCallableReturnsFalseSkipsRule() {
    $rule = (new Rule())->condition(Rule::CALLABLE, function () {
      return FALSE;
    })
      ->require(Rule::TAG_VALUE, 'foo')
      ->require(Rule::TAG_MIN_PER_ENTITY, 1);
    $this->addRule($rule);

    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);
  }

}

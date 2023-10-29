<?php

namespace Drupal\Tests\field_tag\Integration;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\field_tag\Plugin\Validation\Constraint\FieldTagConstraint;
use Drupal\field_tag\Plugin\Validation\Constraint\FieldTagConstraintValidator;
use PHPUnit\Framework\TestCase;
use Drupal\field_tag\Rule\Rule;

/**
 * @covers FieldTagConstraintValidator
 */
final class FieldTagConstraintValidator_ItemLimitsTest extends TestCase {

  use RuleTestTrait;
  use \AKlump\Drupal\PHPUnit\Integration\Framework\MockObject\MockDrupalEntityTrait;

  public function testItemMinOneShowsOneViolationIfTagIsNotUsedOnOneItemOfThree() {
    $this->addRule((new Rule())
      ->condition(Rule::HAS_FIELD, 'field_main')
      ->require(Rule::TAG_REGEX, '/^(english|spanish)$/')
      ->require(Rule::TAG_MIN_PER_ITEM, 1)
    );
    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_main', 'english')
      ->tagDrupalEntity('field_main', 'spanish')
      ->tagDrupalEntity('field_main', 'google')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(1, $violations);
  }

  public function testItemMinOneShowsOneViolationIfTagIsMissing() {
    $this->addRule((new Rule())
      ->condition(Rule::HAS_FIELD, 'field_main')
      ->require(Rule::TAG_VALUE, 'ipsum')
      ->require(Rule::TAG_MIN_PER_ITEM, 1)
    );
    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_main', 'lorem')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(1, $violations);
  }

  public function testItemMinOneWorksIfTagIsUsedOnce() {
    $this->addRule((new Rule())
      ->condition(Rule::TAG_VALUE, 'ipsum')
      ->require(Rule::TAG_MIN_PER_ITEM, 1)
    );
    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_main', 'ipsum')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);
  }

  public function testItemMinOneWorksIfTagIsUsedTwice() {
    $this->addRule((new Rule())
      ->condition(Rule::TAG_VALUE, 'ipsum')
      ->require(Rule::TAG_MIN_PER_ITEM, 1)
    );
    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_main', 'ipsum,ipsum')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);
  }

}

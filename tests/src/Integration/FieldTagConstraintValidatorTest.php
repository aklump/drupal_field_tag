<?php

namespace Drupal\Tests\field_tag\Integration;

use AKlump\Drupal\PHPUnit\Integration\Framework\MockObject\MockDrupalEntityTrait;
use Drupal\field_tag\Plugin\Validation\Constraint\FieldTagConstraintValidator;
use Drupal\field_tag\Rule\Rule;
use PHPUnit\Framework\TestCase;

/**
 * @covers FieldTagConstraintValidator
 */
final class FieldTagConstraintValidatorTest extends TestCase {

  use RuleTestTrait;
  use MockDrupalEntityTrait;

  public function testRequireCorrectEntityWorksWithTagConditionAndMultipleBundlesAndFields() {
    $rule = (new Rule())
      ->condition(Rule::TAG_VALUE, 'foo')
      ->require(Rule::ENTITY, 'node');
    $this->addRule($rule);

    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_main', 'foo')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);

    $entity = $this
      ->createDrupalEntity('node', 'blog_entry')
      ->tagDrupalEntity('field_sidebar', 'foo')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);
  }

  public function testRequireWrongEntityShowsOneViolation() {
    $rule = (new Rule())
      ->condition(Rule::TAG_VALUE, 'foo')
      ->require(Rule::ENTITY, 'node');
    $this->addRule($rule);

    $entity = $this
      ->createDrupalEntity('user', 'user')
      ->tagDrupalEntity('field_first', 'foo')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(1, $violations);
  }

  public function testRequireCorrectEntityBundleFieldWorksWithTagCondition() {
    $this->addRule((new Rule())
      ->condition(Rule::TAG_VALUE, 'foo')
      ->require(Rule::ENTITY, ['node', 'user'], 'in')
      ->require(Rule::BUNDLE, ['page', 'blog'], 'in')
      ->require(Rule::TAGGED_FIELD, ['field_main', 'field_secondary'], 'in')
    );
    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_main', 'foo')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);
  }

  public function testRequireCorrectEntityBundleWorksWithTagCondition() {
    $this->addRule((new Rule())
      ->condition(Rule::TAG_VALUE, 'foo')
      ->require(Rule::ENTITY, 'node')
      ->require(Rule::BUNDLE, 'page')
    );
    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_main', 'foo')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);
  }

  public function testRequireWrongEntityOrBundleShowsViolations() {
    $this->addRule((new Rule())
      ->condition(Rule::TAG_VALUE, 'foo')
      ->require(Rule::ENTITY, 'node')
      ->require(Rule::BUNDLE, 'page')
    );
    $entity = $this
      ->createDrupalEntity('block_content', 'page')
      ->tagDrupalEntity('field_main', 'foo')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(1, $violations);

    $entity = $this
      ->createDrupalEntity('node', 'blog')
      ->tagDrupalEntity('field_main', 'foo')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(1, $violations);

    $entity = $this
      ->createDrupalEntity('user', 'blog')
      ->tagDrupalEntity('field_main', 'foo')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(2, $violations);
  }

  public function testTaggingCorrectEntityBundleAndFieldMatchingRegexWorks() {
    $this->addRule((new Rule())
      ->condition(Rule::TAG_REGEX, '/^(lorem|ipsum|foo|bar)$/')
      ->require(Rule::ENTITY, 'node')
      ->require(Rule::BUNDLE, 'page')
      ->require(Rule::TAGGED_FIELD, 'field_main')
    );
    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_main', 'ipsum')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);
  }

  public function testTagNotMatchingARuleShowsNoViolations() {
    $this->addRule((new Rule())
      ->condition(Rule::TAG_VALUE, 'lorem')
    );
    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_main', 'lorem')
      ->tagDrupalEntity('field_main', 'foo')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);
  }

}

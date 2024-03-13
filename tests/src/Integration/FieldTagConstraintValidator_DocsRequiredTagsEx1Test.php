<?php

namespace Drupal\Tests\field_tag\Integration;

use AKlump\Drupal\PHPUnit\Integration\Framework\MockObject\MockDrupalEntityTrait;
use Drupal\field_tag\Plugin\Validation\Constraint\FieldTagConstraintValidator;
use Drupal\field_tag\Rule\Rule;
use PHPUnit\Framework\TestCase;


/**
 * @covers FieldTagConstraintValidator
 */
final class FieldTagConstraintValidator_DocsRequiredTagsEx1Test extends TestCase {

  use RuleTestTrait;
  use MockDrupalEntityTrait;

  public function dataProviderBundleFieldConditionsTagMinMaxRequirements() {
    $tests = [];
    $tests[] = [
      (new Rule())
        ->condition(Rule::BUNDLE, 'blog_entry')
        ->condition(Rule::HAS_FIELD, 'field_images')
        ->require(Rule::TAG_VALUE, 'thumb')
        ->require(Rule::TAG_MIN_PER_FIELD, 1)
        ->require(Rule::TAG_MAX_PER_FIELD, 1),
    ];

    return $tests;
  }

  /**
   * @dataProvider dataProviderBundleFieldConditionsTagMinMaxRequirements
   */
  public function testAllConditionsMetTagRequirementNotMetViolates(Rule $rule) {
    $this->addRule($rule);
    $entity = $this
      ->createDrupalEntity('node', 'blog_entry', ['field_images'])
      ->tagDrupalEntity('field_foobar', 'thumb')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertBelowMin('thumb', 1, [
      'field_images' => 0,
    ], $violations);
  }

  /**
   * @dataProvider dataProviderBundleFieldConditionsTagMinMaxRequirements
   */
  public function testAllConditionsMetOverMaxRequirementViolates(Rule $rule) {
    $this->addRule($rule);
    $entity = $this
      ->createDrupalEntity('node', 'blog_entry')
      ->tagDrupalEntity('field_images', 'thumb')
      ->tagDrupalEntity('field_images', 'thumb')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertAboveMax('thumb', 1, [
      'field_images' => 2,
    ], $violations);
  }

  /**
   * @dataProvider dataProviderBundleFieldConditionsTagMinMaxRequirements
   */
  public function testAllConditionsMetBelowMinRequiementsViolation(Rule $rule) {
    $this->addRule($rule);
    $entity = $this
      ->createDrupalEntity('node', 'blog_entry', ['field_images'])
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertBelowMin('thumb', 1, [
      'field_images' => 0,
    ], $violations);
  }

  /**
   * @dataProvider dataProviderBundleFieldConditionsTagMinMaxRequirements
   */
  public function testAllConditionsAndRequirementsMetDoesNotViolate(Rule $rule) {
    $this->addRule($rule);
    $entity = $this
      ->createDrupalEntity('node', 'blog_entry')
      ->tagDrupalEntity('field_images', 'thumb')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);
  }

  /**
   * @dataProvider dataProviderBundleFieldConditionsTagMinMaxRequirements
   */
  public function testBundleConditionNotMetDoesNotViolate(Rule $rule) {
    $this->addRule($rule);
    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);

    $entity = $this
      ->createDrupalEntity('node', 'page', ['field_images'])
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);

    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_images', 'thumb')
      ->tagDrupalEntity('field_images', 'thumb')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);
  }

  /**
   * @dataProvider dataProviderBundleFieldConditionsTagMinMaxRequirements
   */
  public function testFieldConditionNotMetDoesNotViolate(Rule $rule) {
    $this->addRule($rule);

    // Under the min
    $entity = $this
      ->createDrupalEntity('node', 'blog_entry', ['field_foobar'])
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);

    // Over the max.
    $entity = $this
      ->createDrupalEntity('node', 'blog_entry')
      ->tagDrupalEntity('field_foobar', 'thumb')
      ->tagDrupalEntity('field_foobar', 'thumb')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);
  }

  /**
   * @dataProvider dataProviderBundleFieldConditionsTagMinMaxRequirements
   */
  public function testChangingOnlyEntityStillViolatesBecauseNoEntityConditionExists(Rule $rule) {
    $this->addRule($rule);

    $entity = $this
      ->createDrupalEntity('node', 'blog_entry', ['field_images'])
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertBelowMin('thumb', 1, [
      'field_images' => 0,
    ], $violations);

    $entity = $this
      ->createDrupalEntity('user', 'blog_entry', ['field_images'])
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertBelowMin('thumb', 1, [
      'field_images' => 0,
    ], $violations);

    $entity = $this
      ->createDrupalEntity('node', 'blog_entry')
      ->tagDrupalEntity('field_images', 'thumb')
      ->tagDrupalEntity('field_images', 'thumb')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertAboveMax('thumb', 1, [
      'field_images' => 2,
    ], $violations);

    $entity = $this
      ->createDrupalEntity('user', 'blog_entry')
      ->tagDrupalEntity('field_images', 'thumb')
      ->tagDrupalEntity('field_images', 'thumb')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertAboveMax('thumb', 1, [
      'field_images' => 2,
    ], $violations);
  }

}

<?php

namespace Drupal\Tests\field_tag\Integration;

use AKlump\Drupal\PHPUnit\Integration\Framework\MockObject\MockDrupalEntityTrait;
use Drupal\field_tag\Plugin\Validation\Constraint\FieldTagConstraintValidator;
use Drupal\field_tag\Rule\Rule;
use PHPUnit\Framework\TestCase;
use Drupal\Tests\field_tag\Integration\TestTraits\RuleTestTrait;

/**
 * @covers FieldTagConstraintValidator
 */
final class FieldTagConstraintValidator_DocsRequiredTagsEx2Test extends TestCase {

  use RuleTestTrait;
  use MockDrupalEntityTrait;

  public function testComparisonOfTaggedFieldVsHasField() {
    $entity = $this->createDrupalEntity('user', 'user', [
      'field_images',
      'field_pdfs',
    ])
      ->tagDrupalEntity('field_other', 'teaser')
      ->mockDrupalEntity();

    $this->addRule((new Rule())
      ->condition(Rule::ENTITY, ['node', 'user'], 'in')
      ->condition(Rule::HAS_FIELD, ['field_images', 'field_pdfs'], 'in')
      ->require(Rule::TAG_VALUE, 'teaser')
      ->require(Rule::TAGGED_FIELD, ['field_images', 'field_pdfs'], 'in')
      ->require(Rule::TAG_MIN_PER_ENTITY, 1));
    // Field requirement not met, tag is is on a different field.
    $violations = $this->validateDrupalEntity($entity);
    $this->assertSame(1, count($violations));

    $this->deleteAllRules();
    $this->addRule((new Rule())
      ->condition(Rule::ENTITY, ['node', 'user'], 'in')
      ->condition(Rule::HAS_FIELD, ['field_images', 'field_pdfs'], 'in')
      ->require(Rule::TAG_VALUE, 'teaser')
      ->require(Rule::TAG_MIN_PER_ENTITY, 1));
    // No requirement not met, so all conditions and requirements are met.
    $violations = $this->validateDrupalEntity($entity);
    $this->assertSame(0, count($violations));
  }

  public function dataMinOneTeaserPerMultiFieldsMultiEntitiesNoBundleCondition() {
    $tests = [];
    $tests[] = [
      (new Rule())
        ->condition(Rule::ENTITY, ['node', 'user'], 'in')
        ->condition(Rule::HAS_FIELD, ['field_images', 'field_pdfs'], 'in')
        ->require(Rule::TAG_VALUE, 'teaser')
        ->require(Rule::TAGGED_FIELD, ['field_images', 'field_pdfs'], 'in')
        ->require(Rule::TAG_MIN_PER_ENTITY, 1),
    ];
    $tests[] = [
      (new Rule())
        ->condition(Rule::ENTITY, ['node', 'user'], 'in')
        ->condition(Rule::HAS_FIELD, ['field_images', 'field_pdfs'], 'in')
        ->require(Rule::TAG_VALUE, 'teaser')
        ->require(Rule::TAG_MIN_PER_ENTITY, 1),
    ];

    return $tests;
  }

  /**
   * @dataProvider dataMinOneTeaserPerMultiFieldsMultiEntitiesNoBundleCondition
   */
  public function testFieldConditionNotMetDoesNotViolate(Rule $rule) {
    $this->addRule($rule);

    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);

    $entity = $this
      ->createDrupalEntity('user', 'page')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);

    $entity = $this
      ->createDrupalEntity('user', 'page')
      ->tagDrupalEntity('field_foo', 'teaser')
      ->tagDrupalEntity('field_foo', 'teaser')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);
  }

  /**
   * @dataProvider dataMinOneTeaserPerMultiFieldsMultiEntitiesNoBundleCondition
   */
  public function testTagConditionMetOnFieldPdfsNoViolations(Rule $rule) {
    $this->addRule($rule);

    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_pdfs', 'teaser')
      ->mockDrupalEntity();

    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);
  }


  /**
   * @dataProvider dataMinOneTeaserPerMultiFieldsMultiEntitiesNoBundleCondition
   */
  public function testTagConditionMetOnFieldImagesNoViolations(Rule $rule) {
    $this->addRule($rule);

    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_images', 'teaser')
      ->mockDrupalEntity();

    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);
  }

  /**
   * @dataProvider dataMinOneTeaserPerMultiFieldsMultiEntitiesNoBundleCondition
   */
  public function testBothTagRequirementsMetOnceOverEntityMinNoViolations(Rule $rule) {
    $this->addRule($rule);

    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_images', 'teaser')
      ->tagDrupalEntity('field_pdfs', 'teaser')
      ->mockDrupalEntity();

    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);
  }

  /**
   * @dataProvider dataMinOneTeaserPerMultiFieldsMultiEntitiesNoBundleCondition
   */
  public function testEntityConditionNotMetDoesNotViolate(Rule $rule) {
    $this->addRule($rule);

    $entity = $this
      ->createDrupalEntity('user', 'user')
      ->mockDrupalEntity();

    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);
  }

  /**
   * @dataProvider dataMinOneTeaserPerMultiFieldsMultiEntitiesNoBundleCondition
   */
  public function testAllConditionsMetNoTagViolates(Rule $rule) {
    $this->addRule($rule);

    $entity = $this
      ->createDrupalEntity('node', 'page', ['field_images'])
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertBelowMin('teaser', 1, [
      'page' => 0,
    ], $violations);

    $entity = $this
      ->createDrupalEntity('user', 'page', ['field_images'])
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertBelowMin('teaser', 1, [
      'page' => 0,
    ], $violations);

    $entity = $this
      ->createDrupalEntity('node', 'page', ['field_pdfs'])
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertBelowMin('teaser', 1, [
      'page' => 0,
    ], $violations);

    $entity = $this
      ->createDrupalEntity('user', 'page', ['field_pdfs'])
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertBelowMin('teaser', 1, [
      'page' => 0,
    ], $violations);
  }

}

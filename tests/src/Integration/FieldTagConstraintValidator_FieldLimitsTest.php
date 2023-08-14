<?php

namespace Drupal\Tests\field_tag\Integration;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\field_tag\Plugin\Validation\Constraint\FieldTagConstraint;
use Drupal\field_tag\Plugin\Validation\Constraint\FieldTagConstraintValidator;
use Drupal\field_tag\Rule\Rule;
use PHPUnit\Framework\TestCase;

/**
 * @covers FieldTagConstraintValidator
 */
final class FieldTagConstraintValidator_FieldLimitsTest extends TestCase {

  use RuleTestTrait;
  use \AKlump\PHPUnit\Framework\MockObject\MockDrupalEntityTrait;

  /**
   * Provides data for testKnowledgePageRequiredTagsThumb.
   */
  public function dataForTestKnowledgeExampleRequiredTagThumbProvider() {
    $tests = [];

    // Assert usage on another field does not violate max
    $tests[] = [
      $this->createDrupalEntity('node', 'blog_entry')
        ->tagDrupalEntity('field_other', 'thumb')
        ->tagDrupalEntity('field_other', 'thumb')
        ->mockDrupalEntity(),
      function ($violations) {
        $this->assertCount(0, $violations);
      },
    ];

    // Not all conditions met, no violation.
    $tests[] = [
      $this->createDrupalEntity('node', 'blog_entry')
        ->mockDrupalEntity(),
      function ($violations) {
        $this->assertCount(0, $violations);
      },
    ];

    // Assert within field min/max works.
    $tests[] = [
      $this->createDrupalEntity('node', 'blog_entry')
        ->tagDrupalEntity('field_images', 'thumb')
        ->mockDrupalEntity(),
      function ($violations) {
        $this->assertCount(0, $violations);
      },
    ];

    // Assert over field_max violates.
    $tests[] = [
      $this->createDrupalEntity('node', 'blog_entry')
        ->tagDrupalEntity('field_images', 'thumb')
        ->tagDrupalEntity('field_images', 'thumb')
        ->mockDrupalEntity(),
      function ($violations) {
        $this->assertCount(1, $violations);
      },
    ];

    return $tests;
  }

  /**
   * @dataProvider dataForTestKnowledgeExampleRequiredTagThumbProvider
   */
  public function testKnowledgePageRequiredTagsThumb(EntityInterface $entity, callable $assertions) {
    $this->addRule((new Rule())
      ->condition(Rule::TAG_VALUE, 'thumb')
      ->condition(Rule::ENTITY, 'node')
      ->condition(Rule::BUNDLE, 'blog_entry')
      ->condition(Rule::HAS_FIELD, 'field_images')
      ->require(Rule::TAG_MIN_PER_FIELD, 1)
      ->require(Rule::TAG_MAX_PER_FIELD, 1)
    );

    $violations = $this->validateDrupalEntity($entity);
    $assertions($violations);
  }

  public function testFieldMinOneForThreeMissingFieldsGivesViolationForEachField() {
    $this->addRule((new Rule())
      ->condition(Rule::BUNDLE, 'page')
      ->require(Rule::TAG_VALUE, 'hornet')
      ->require(Rule::TAG_MIN_PER_FIELD, 1)
    );

    $entity = $this
      ->createDrupalEntity('node', 'page', [
          'field_one',
          'field_two',
          'field_three',
        ]
      )
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertBelowMin('hornet', 1,
      [
        'field_one' => 0,
        'field_two' => 0,
        'field_three' => 0,
      ], $violations);
  }

  public function testFieldMinTwoShowsOneViolationIfTagIsUsedOnce() {
    $this->addRule((new Rule())
      ->condition(Rule::TAG_VALUE, 'thumb')
      ->require(Rule::TAG_MIN_PER_FIELD, 2)
    );
    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_main', 'thumb')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertBelowMin(
      'thumb',
      2,
      ['field_main' => 1],
      $violations
    );
  }

  public function testFieldMinOneWorksIfTagIsUsedOnce() {
    $this->addRule((new Rule())
      ->condition(Rule::TAG_VALUE, 'lorem')
      ->require(Rule::TAG_MIN_PER_FIELD, 1)
    );
    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_main', 'lorem')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);
  }

  public function testFieldMinOneWorksIfTagIsUsedTwiceOnSameField() {
    $this->addRule((new Rule())
      ->condition(Rule::TAG_VALUE, 'lorem')
      ->require(Rule::TAG_MIN_PER_FIELD, 1)
    );

    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_main', 'lorem')
      ->tagDrupalEntity('field_main', 'lorem')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);
  }

  public function testFieldMinOneWorksIfTagIsUsedOnSecondFieldItemOnly() {
    $this->addRule((new Rule())
      ->condition(Rule::TAG_VALUE, 'bar')
      ->require(Rule::TAG_MIN_PER_FIELD, 1)
    );

    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_main', 'foo')
      ->tagDrupalEntity('field_main', 'bar')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);
  }

  public function testFieldMaxZeroShowsOneViolationIfTagIsUsed() {
    $this->addRule((new Rule())
      ->condition(Rule::TAG_VALUE, 'lorem')
      ->require(Rule::TAG_MAX_PER_FIELD, 0)
    );
    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_main', 'lorem')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(1, $violations);
  }

  public function testFieldMaxOneWorksIfTagIsUsedOnce() {
    $this->addRule((new Rule())
      ->condition(Rule::TAG_VALUE, 'lorem')
      ->require(Rule::TAG_MIN_PER_FIELD, 1)
    );
    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_main', 'lorem')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);
  }

  public function testFieldMaxOneShowsOneViolationIfTagIsUsedTwice() {
    $this->addRule((new Rule())
      ->condition(Rule::TAG_VALUE, 'lorem')
      ->require(Rule::TAG_MAX_PER_FIELD, 1)
    );
    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_main', 'lorem')
      ->tagDrupalEntity('field_main', 'lorem')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(1, $violations);
  }

  public function testFieldMaxOneShowsOneViolationIfTagIsUsedOnItemsTwoAndThreeOnly() {
    $this->addRule((new Rule())
      ->condition(Rule::TAG_VALUE, 'lorem')
      ->require(Rule::TAG_MAX_PER_FIELD, 1)
    );
    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_main', 'foo')
      ->tagDrupalEntity('field_main', 'lorem')
      ->tagDrupalEntity('field_main', 'lorem')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(1, $violations);
  }

}

<?php

namespace Drupal\Tests\field_tag\Integration;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\field_tag\Plugin\Validation\Constraint\FieldTagConstraint;
use Drupal\field_tag\Plugin\Validation\Constraint\FieldTagConstraintValidator;
use Drupal\field_tag\Rule\Rule;
use PHPUnit\Framework\TestCase;

/**
 * @covers FieldTagConstraintValidator
 */
final class FieldTagConstraintValidator_EntityLimitsTest extends TestCase {

  use RuleTestTrait;
  use \AKlump\Drupal\PHPUnit\Integration\Framework\MockObject\MockDrupalEntityTrait;

  public function testEntityMinNotMetIfFieldDoesNotMatchRequirement() {
    $this->addRule((new Rule())
      ->condition(Rule::ENTITY, ['node', 'user'], 'in')
      ->require(Rule::TAG_VALUE, 'teaser')
      ->require(Rule::TAGGED_FIELD, ['field_images', 'field_pdfs'], 'in')
      ->require(Rule::TAG_MIN_PER_ENTITY, 1));

    // Conditions met, tag is on a different field than conditions.
    $entity = $this->createDrupalEntity('user', 'user')
      ->tagDrupalEntity('field_other', 'teaser')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(2, $violations);
    $this->assertBelowMin('teaser', 1, ['user' => 0], $violations);
  }

  public function testEntityMinTwoShowsOneViolationIfTagIsUsedOnce() {
    $this->addRule((new Rule())
      ->condition(Rule::TAG_VALUE, 'lorem')
      ->require(Rule::TAG_MIN_PER_ENTITY, 2)
    );
    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_main', 'lorem')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(1, $violations);
  }

  public function testEntityMinOneWorksIfTagIsUsedOnce() {
    $this->addRule((new Rule())
      ->condition(Rule::TAG_VALUE, 'lorem')
      ->require(Rule::TAG_MIN_PER_ENTITY, 1)
    );
    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_main', 'lorem')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);
  }

  public function testEntityMinOneWorksIfTagIsUsedTwice() {
    $this->addRule((new Rule())
      ->condition(Rule::TAG_VALUE, 'lorem')
      ->require(Rule::TAG_MIN_PER_ENTITY, 1)
    );
    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_main', 'lorem')
      ->tagDrupalEntity('field_main', 'lorem')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);
  }

  public function testEntityMaxZeroShowsOneViolationIfTagIsUsed() {
    $this->addRule((new Rule())
      ->condition(Rule::TAG_VALUE, 'lorem')
      ->require(Rule::TAG_MAX_PER_ENTITY, 0)
    );
    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_main', 'lorem')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(1, $violations);
  }

  public function testEntityMaxOneWorksIfTagIsUsedOnce() {
    $this->addRule((new Rule())
      ->condition(Rule::TAG_VALUE, 'lorem')
      ->require(Rule::TAG_MAX_PER_ENTITY, 1)
    );
    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_main', 'lorem')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);
  }

  public function testEntityMaxOneShowsOneViolationIfTagIsUsedTwice() {
    $this->addRule((new Rule())
      ->condition(Rule::TAG_VALUE, 'lorem')
      ->require(Rule::TAG_MAX_PER_ENTITY, 1)
    );
    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_main', 'lorem')
      ->tagDrupalEntity('field_main', 'lorem')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(1, $violations);
  }

  public function testRegexWorksAsExpectedWithMinPerField() {
    $this->addRule((new Rule)
      ->condition(Rule::TAG_REGEX, '/^(lorem|ipsum|foo|bar)$/')
      ->require(Rule::TAG_MIN_PER_FIELD, 2)
    );

    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_main', 'lorem')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(1, $violations);

    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_main', 'lorem')
      ->tagDrupalEntity('field_main', 'lorem')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);

    $entity = $this
      ->createDrupalEntity('node', 'page')
      ->tagDrupalEntity('field_main', 'lorem')
      ->tagDrupalEntity('field_main', 'ipsum')
      ->tagDrupalEntity('field_main', 'ipsum')
      ->mockDrupalEntity();
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);
  }

}

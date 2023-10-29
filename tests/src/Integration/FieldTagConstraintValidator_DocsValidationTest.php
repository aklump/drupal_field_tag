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
final class FieldTagConstraintValidator_DocsValidationTest extends TestCase {

  use RuleTestTrait;
  use \AKlump\Drupal\PHPUnit\Integration\Framework\MockObject\MockDrupalEntityTrait;

  public function dataForFirstRuleHappyPath() {
    $tests = [];
    $tests[] = [
      $this
        ->createDrupalEntity('node', 'blog_entry')
        ->tagDrupalEntity('field_photos', 'thumb')
        ->mockDrupalEntity(),
    ];
    $tests[] = [
      $this
        ->createDrupalEntity('node', 'blog_entry')
        ->tagDrupalEntity('field_photos', 'thumb')
        ->tagDrupalEntity('field_photos', 'thumb')
        ->mockDrupalEntity(),
    ];
    $tests[] = [
      $this
        ->createDrupalEntity('node', 'page')
        ->tagDrupalEntity('field_photos', 'thumb')
        ->mockDrupalEntity(),
    ];
    $tests[] = [
      $this
        ->createDrupalEntity('node', 'page')
        ->tagDrupalEntity('field_photos', 'thumb')
        ->tagDrupalEntity('field_photos', 'thumb')
        ->mockDrupalEntity(),
    ];

    return $tests;
  }

  /**
   * @dataProvider dataForFirstRuleHappyPath
   */
  public function testFirstRuleHappyPaths(EntityInterface $entity) {
    $this->addRule((new Rule())
      ->condition(Rule::TAG_VALUE, 'thumb')
      ->condition(Rule::ENTITY, 'node')
      ->condition(Rule::BUNDLE, ['page', 'blog_entry'], 'in')
      ->condition(Rule::HAS_FIELD, 'field_photos')
      ->require(Rule::TAG_MIN_PER_FIELD, 1));
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);
  }

  public function dataForSecondRuleHappyPath() {
    $tests = [];
    $tests[] = [
      $this
        ->createDrupalEntity('node', 'blog_entry')
        ->tagDrupalEntity('field_articles', 'english')
        ->mockDrupalEntity(),
    ];
    $tests[] = [
      $this
        ->createDrupalEntity('node', 'blog_entry')
        ->tagDrupalEntity('field_articles', 'spanish')
        ->mockDrupalEntity(),
    ];
    $tests[] = [
      $this
        ->createDrupalEntity('node', 'blog_entry')
        ->tagDrupalEntity('field_articles', 'french')
        ->mockDrupalEntity(),
    ];

    return $tests;
  }

  /**
   * @dataProvider dataForSecondRuleHappyPath
   */
  public function testSecondRuleHappyPath(EntityInterface $entity) {
    $this->addRule((new Rule())
      ->condition(Rule::TAG_REGEX, '/^(english|spanish|french)$/')
      ->require(Rule::ENTITY, 'node')
      ->require(Rule::TAGGED_FIELD, 'field_articles'));
    $violations = $this->validateDrupalEntity($entity);
    $this->assertCount(0, $violations);
  }

}

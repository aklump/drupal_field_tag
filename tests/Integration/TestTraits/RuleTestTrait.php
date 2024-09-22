<?php

namespace Drupal\Tests\field_tag\Integration\TestTraits;

use AKlump\Drupal\PHPUnit\Integration\Framework\MockObject\MockDrupalEntityTrait;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\field_tag\FieldTagService;
use Drupal\field_tag\Helpers\FormatUsageValue;
use Drupal\field_tag\Plugin\Validation\Constraint\FieldTagConstraint;
use Drupal\field_tag\Plugin\Validation\Constraint\FieldTagConstraintValidator;
use Drupal\field_tag\Service\LabelService;
use Drupal\field_tag\Service\ValidationService;
use Drupal\field_tag\Tags;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait RuleTestTrait {

  use MockDrupalEntityTrait;

  /** @var array */
  protected $rules;

  /**
   * @var array
   */
  protected $drupalEntity;

  protected $container;

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return \Drupal\field_tag\Plugin\Validation\Violations\ViolationBase[]
   */
  protected function validateDrupalEntity(EntityInterface $entity): array {
    $field_tag_service = $this->createConfiguredMock(FieldTagService::class, [
      'getTaggableFieldNamesByEntity' => array_keys($entity->getFields()),
    ]);
    $this->container->set('field_tag', $field_tag_service);

    $field_tag_labels_service = $this->createConfiguredMock(LabelService::class, [
      'scope' => [],
    ]);
    $this->container->set('field_tag.labels', $field_tag_labels_service);


    $validation_service = $this->createConfiguredMock(ValidationService::class, [
      'getAllValidationRules' => $this->rules,
    ]);
    $this->container->set('field_tag.validation', $validation_service);

    $validator = FieldTagConstraintValidator::create($this->container);
    $context = $this->createMock(ExecutionContextInterface::class);

    $violations = [];
    $context->method('addViolation')
      ->willReturnCallback(function () use (&$violations) {
        list($message, $params) = func_get_args();
        foreach ($params as $find => $replace) {
          $message = str_replace($find, $replace, $message);
        }
        $violations[] = $message;
      });

    $validator->initialize($context);

    $constraint = new FieldTagConstraint();
    $validator->validate($entity, $constraint);

    return $violations;
  }

  protected function createDrupalEntity(string $entity_type_id, string $bundle, array $field_names = []): self {
    $this->drupalEntity = [
      'entity' => $entity_type_id,
      'bundle' => $bundle,
      'fields' => array_fill_keys($field_names, []),
    ];

    return $this;
  }

  /**
   * Each time you call this the field_item_list gets appended
   *
   * @param string $field_name
   * @param string $tags
   *   CSV of one or more tags on this field_list_item.
   *
   * @return $this
   */
  public function tagDrupalEntity(string $field_name, string $tags): self {
    if (empty($tags)) {
      throw new \InvalidArgumentException('Invalid value for $tags');
    }
    $this->drupalEntity['fields'][$field_name][] = [
      'field_tag' => (string) (new Tags($tags)),
    ];

    return $this;
  }

  protected function mockDrupalEntity(): EntityInterface {
    $data = $this->drupalEntity;
    unset($this->drupalEntity);

    return $this->createEntityMock(
      $data['entity'],
      $data['bundle'],
      $data['fields']
    );
  }

  protected function deleteAllRules(): self {
    $this->rules = [];

    return $this;
  }

  protected function addRule($rule): self {
    $this->rules[] = $rule;

    return $this;
  }

  protected function setUp(): void {
    $this->container = new ContainerBuilder();
    \Drupal::setContainer($this->container);
    $this->rules = [];
  }

  public function assertBelowMin(string $expected_tag, int $expected_value, array $scope_counts, array $violations) {
    $args = func_get_args();
    array_unshift($args, (new FieldTagConstraint())->belowMinMessage);

    $this->helperAssertBelowAbove(...$args);
  }

  public function assertAboveMax(string $expected_tag, int $expected_value, array $scope_counts, array $violations) {
    $args = func_get_args();
    array_unshift($args, (new FieldTagConstraint())->aboveMaxMessage);

    $this->helperAssertBelowAbove(...$args);
  }

  private function helperAssertBelowAbove(string $expected_message, string $expected_tag, int $expected_value, array $scope_counts, array $violations): void {

    // We should only count the min/max violations.
    $violations = array_filter($violations, function (string $violation) {
      return preg_match('/minimum|maximum/', $violation);
    });

    if (empty($scope_counts)) {
      throw new \InvalidArgumentException('$scope_counts cannot be empty');
    }

    // Assert correct number of violations.
    $this->assertCount(\count($scope_counts), $violations);

    // Assert the expected full messages.
    foreach ($scope_counts as $scope => $scope_count) {
      $params = [
        '%tag' => $expected_tag,
        ':actual' => (new FormatUsageValue())($scope_count),
        ':expected' => (new FormatUsageValue())($expected_value),
        '%scope' => $scope,
      ];
      $expected_message = str_replace(array_keys($params), $params, $expected_message);
      $this->assertContains($expected_message, $violations);
    }
  }

}

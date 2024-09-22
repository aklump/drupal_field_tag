<?php

namespace Drupal\field_tag\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Drupal\field_tag\Rule\CheckRequirements;
use Drupal\field_tag\Rule\CheckConditions;

/**
 * Validates the Field Tag constraint.
 */
class FieldTagConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * @var \Drupal\field_tag\Service\ValidationService
   */
  protected $validationService;

  /**
   * @var \Drupal\field_tag\FieldTagService
   */
  protected $fieldTagService;

  /**
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * @var array
   */
  protected $rules;

  private $fieldName = '';

  /**
   * @var \Drupal\field_tag\Service\LabelService
   */
  private $labelService;

  /**
   * @var \Symfony\Component\Validator\Constraint
   */
  private Constraint $constraint;

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    $obj = new static();
    $obj->validationService = $container->get('field_tag.validation');
    $obj->fieldTagService = $container->get('field_tag');
    $obj->labelService = $container->get('field_tag.labels');

    return $obj;
  }

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    $this->constraint = $constraint;
    $this->entity = $value;
    $this->rules = $this->validationService->getAllValidationRules();
    $field_names = $this->fieldTagService->getTaggableFieldNamesByEntity($this->entity);
    $check_conditions = new CheckConditions();
    $check_requirements = new CheckRequirements($this->fieldTagService, $this->labelService, $this->context);

    // It is important to keep the correct order when checking violations.
    // Violations must be checked from the inside out; that is to say, first
    // field items, then field_item_lists, then the entity.  Otherwise the
    // violations are misapplied.
    foreach ($field_names as $field_name) {
      if (!$this->entity->hasField($field_name)) {
        continue;
      }

      // Check every rule with the context of the field_item(s)...
      foreach ($this->rules as $rule) {
        $field_item_list = $this->entity->get($field_name);

        // 1. First check all field items.
        foreach ($field_item_list as $item) {
          if ($check_conditions($rule, $item)) {
            $check_requirements($rule, $item);
          }
        }

        // 2. Next check the field_item_list.
        if ($check_conditions($rule, $field_item_list)) {
          $check_requirements($rule, $field_item_list);
        }
      }
    }

    // 3. Check the entity once per each rule.
    foreach ($this->rules as $rule) {
      if ($check_conditions($rule, $this->entity)) {
        $check_requirements($rule, $this->entity);
      }
    }
  }

}

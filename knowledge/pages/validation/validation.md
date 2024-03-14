<!--
id: validation
tags: api
-->

# Tag Validation Rules

It's possible to setup rules for how tags are used including, minimums, maximums, required, or invalid. Without defining these validation rules anything can be entered as a tag without constraint.

To define rules you will implement `hook_field_tag_validation_rules`; see _field\_tag.api.php_ for more info and examples. Every rule change requires that you rebuild the Drupal caches.

The rules are implemented as [entity and field constraints](https://www.drupal.org/docs/drupal-apis/entity-api/entity-validation-api/entity-validation-api-overview). @see `\Drupal\field_tag\Plugin\Validation\Constraint\FieldTagConstraint`.

## Explained

Each rule consists of two parts: 1) the conditions to be met to apply the rule and 2) the requirements that must be met if the rule is applied.

## When are Rules Applied?

When the entity is saved.

## When Does a Rule Apply

When all the `condition` statements are `TRUE`.

## Must All `condition` clauses be `TRUE`

Yes, or the rule is skipped.

## Must All `require` clauses be `TRUE`

Yes, or the rule is in violation.

## Callable Condition Explained

The trump condition is the `Drupal\field_tag\Rule\Rule::CALLABLE`. Here's how you might use that. Notice the arguments may be `NULL`.  If you do not return a value, `FALSE` is assumed and the condition is considered unmet, and the rule skipped.  **Each rule may have only one callable condition.**

```php
$callable = function (
  ?\Drupal\Core\Entity\EntityInterface $entity,
  ?\Drupal\Core\Field\FieldItemListInterface $item_list
): bool {
  // Do something that returns a bool.
};

$rule = (new Drupal\field_tag\Rule\Rule())
  ->condition(Drupal\field_tag\Rule\Rule::CALLABLE, $callable)
  ->require(Drupal\field_tag\Rule\Rule::TAG_VALUE, 'foo')
  ->require(Drupal\field_tag\Rule\Rule::TAG_MIN_PER_ENTITY, 1);
```

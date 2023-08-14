<?php

namespace Drupal\field_tag\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that field tags on an entity follow all rules.
 *
 * For every rule that matches a tag:
 * - check the entity, bundle and field requirements.
 * - check the min/max requirements for all scopes.
 *
 * @Constraint(
 *   id = "FieldTagFieldTag",
 *   label = @Translation("Field Tag", context = "Validation"),
 *   type = "string"
 * )
 */
class FieldTagConstraint extends Constraint {

  const SCOPE_ENTITY = 1;

  const SCOPE_BUNDLE = 2;

  const SCOPE_FIELD = 3;

  const SCOPE_FIELD_ITEM = 4;

  public $belowMinMessage = 'The tag %tag is used :actual per %scope, which is less than the minimum of :expected per %scope.';

  public $aboveMaxMessage = 'The tag %tag is used :actual per %scope, which exceeds the maximum of :expected per %scope.';

  public $badScopeMessage = 'It is not allowed to tag any %scope with the tag %tag.';

}

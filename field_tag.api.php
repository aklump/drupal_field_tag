<?php

/**
 * @file
 * Defines the API functions provided by the field_tag module.
 */

// Get the tag value for a field.
$tag = FieldTag::loadFromParent($entity, 'field_foo');

// Get the tags for a multi-value field.
$tags = [
  FieldTag::loadFromParent($entity, 'field_foo', 0),
  FieldTag::loadFromParent($entity, 'field_foo', 1),
  FieldTag::loadFromParent($entity, 'field_foo', 2),
];

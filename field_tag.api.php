<?php

/**
 * @file
 * Defines the API functions provided by the field_tag module.
 */

// Load the tag value for a field.
$field_tag = FieldTag::loadFromParent($entity, 'field_foo');
$value = $field_tag->getTag();

// Load the tag value for a field.
$value = (string) FieldTag::loadFromParent($entity, 'field_foo');

// Get the tags for a multi-value field.
$tags = [
  (string) FieldTag::loadFromParent($entity, 'field_foo', 0),
  (string) FieldTag::loadFromParent($entity, 'field_foo', 1),
  (string) FieldTag::loadFromParent($entity, 'field_foo', 2),
];

// To programmatically tag a field, then save the entity.
$node->get('field_images')->get(0)->field_tag = 'foo bar baz';
$node->save();

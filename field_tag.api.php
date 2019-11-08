<?php

/**
 * @file
 * Defines the API functions provided by the field_tag module.
 */

use Drupal\field_tag\Entity\FieldTag;

/**
 * Programmatically tagging fields.
 *
 * This should look very familiar and simple.
 */

$entity->get('field_images')->get(0)->field_tag = 'foo, bar, baz';
$entity->save();

/**
 * Programmatically manipulating field tags.
 *
 * There are two approaches for working with tags already attached to an
 * entity. Each one has it's merits so review both and then decide which is
 * most
 * appropriate for your use case.
 *
 * The first approach shows loading a given tag entity directly.  This method
 * is nice because if the field is not already tagged--that is the field_tag
 * entity does not yet exist--you still receive a configured instance of a
 * field_tag entity in return (not yet saved, however).
 *
 * In both methods, once the field_tag entity is provided, you work with it the
 * same, using on it's own methods.
 */
$field_tag_entity = FieldTag::loadFromParent($entity, 'field_images', 0);

// Check if a tag exists based on the CSV split.
$has_tag = $field_tag_entity->hasTag('hero');

// Get the string value of the field_tag.  Both yield the same result.
$value = $field_tag_entity->getValue();
$value = (string) $field_tag_entity;

// Get an array of tags split by CSV.
$tags = $field_tag_entity->getTags();

/**
 * Second approach, first attach all tags to parent, then manipulate.
 *
 * The alternative approach is to attach all field_tags to an entity and then
 * work with them in that context.  An example follows.
 */
\Drupal::service('field_tag')->attachTags($entity);
foreach ($entity->get('field_images') as $item) {
  if ($field_tag_entity = $item->field_tag) {
    $has_tag = $field_tag_entity->hasTag();
    $tags = $field_tag_entity->getTags();
    $value = $field_tag_entity->getValue();
  }
}

/**
 * Pluck out a single image tagged by 'cover'.
 */
$cover_image = \Drupal::service('field_tag')
  ->attachTags($entity)
  ->getItemsTaggedBy('cover', 'field_images');
if ($cover_image) {
  $uri = $cover_image[0]->uri;
}

/**
 * Another version, more verbose.
 *
 * Get the image entity and URI of the first image tagged with 'hero'.  It is
 * assumed that $entity has an image reference field: field_images, and field
 * tagging has been configured for that field, and at least one field has been
 * tagged with 'hero'.
 */
$hero_image_entity = NULL;
$hero_image_uri = NULL;
foreach ($entity->get('field_images') as $image_item) {
  if ($image_item->field_tag && $image_item->field_tag->hasTag('hero')) {
    $hero_image_entity = $image_item->get('entity')->getTarget()->getValue();
    $hero_image_uri = $hero_image_entity->get('uri')->value;
    break;
  }
}

/**
 * A complex Paragraphs example.
 *
 * In this example, a node has a paragraph reference as field_paragraphs, which
 * allows multiple items.  Tagging is set up on field_paragraphs.  (That is, on
 * the node, not the paragraph entity.)  When given the paragraph, we check to
 * see if it is tagged on the parent by a given tag and then do something to it.
 */
$parent = $paragraph->getParentEntity();
$parent_field = $paragraph->get('parent_field_name')->get(0)->value;
$items = \Drupal::service('field_tag')
  ->attachTags($parent)
  ->getItemsTaggedBy('golden', $parent_field);
foreach ($items as $item) {
  if ($item->target_id == $paragraph->id()) {
    // React to the fact that this paragraph is tagged by 'golden'.
  }
}

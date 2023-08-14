<?php

/**
 * @file
 * Defines the API functions provided by the field_tag module.
 */

// There are two classes to consider when working with field tags.  The
// low-level class \Drupal\field_tag\Tags is purely utilitarian and has no
// dependencies and may be more intuitive in some cases; it has not concept of
// parent entity.  The other class, which represents the FieldTag entity is
// \Drupal\field_tag\Entity\FieldTag and should also be taken into account.

/**
 * Create a field tag object attached to a parent entity.
 */
$instance = \Drupal\field_tag\Entity\FieldTag::createFromTags(
  \Drupal\field_tag\Tags::create('foo,bar'),
  $parent_entity,
  'field_foo',
);

/**
 * Create a field tag object by array
 */
$array_of_tags = ['foo', 'bar'];
$instance = \Drupal\field_tag\Entity\FieldTag::createFromTags(
  \Drupal\field_tag\Tags::create(...$array_of_tags),
  $parent_entity,
  'field_foo',
);
TRUE === $instance->hasTag('foo');

/**
 * Programmatically tagging fields.
 *
 * You must use ->field_tag if you want the entity save operation to persist the
 * values.  You cannot use ->fieldTag for adding tags to be saved in the
 * database.
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
$field_tag_entity = \Drupal\field_tag\Entity\FieldTag::loadByParentField($entity, 'field_images', 0);

// Check if a tag exists based on the CSV split.
$has_tag = $field_tag_entity->hasTag('hero');

// Get the string value of the field_tag.  Both yield the same result.
$value = $field_tag_entity->getValue();
$value = (string) $field_tag_entity;

// Get an array of tags split by CSV.
$tags = $field_tag_entity->all();

/**
 * Second approach, first attach all tags to parent, then manipulate.
 *
 * The alternative approach is to attach all field_tags to an entity and then
 * work with them in that context.  An example follows.
 */
\Drupal::service('field_tag')->attachTags($entity);
foreach ($entity->get('field_images') as $item) {
  if ($field_tag_entity = $item->fieldTag) {
    $has_tag = $field_tag_entity->hasTag();
    $tags = $field_tag_entity->all();
    $value = $field_tag_entity->getValue();
  }
}

/**
 * Pluck out a single image tagged by 'hero'.
 */
$hero_image_references = \Drupal::service('field_tag')
  ->attachTags($node)
  ->getItemsTaggedBy('hero', 'field_images');
if ($hero_image_references && ($image_file = $hero_images[0]->get('entity')
    ->getTarget()
    ->getEntity())) {
  $uri = $image_file->getFileUri();
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

/**
 * Getting tags for a referenced paragraph.
 *
 * In this example we want to find the first tag that begins with '#' to use as
 * an id.  We're assuming that a tag such as "#foo" has been entered on the
 * parent's reference field.
 */
// First get an array of \Drupal\field_tag\Entity\FieldTag entities.
$field_tag_entities = $this->service('field_tag')
  ->getFieldTagsByParagraph($paragraph);

// Then merge them into a single array of single tag strings.
$tags = array_flatten(array_map(function ($field_tag) {
  return $field_tag->all();
}, $field_tag_entities));

// Now we want to get the first tag that begins with '#', so we can use that to
// render an id attribute.
$id = array_values(array_filter($tags, function ($tag) {
  return strpos($tag, '#') === 0;
}))[0];

/**
 * Implements hook_field_tag_tags_info().
 *
 * Provides information about the available field tags and facilitates grouping.
 *
 * @return array
 *   An array with these keys:
 *   - types
 *   - tags
 *   Types should be keyed by the machine name that is used to key the tags
 *   array.  Each value in types should contain:
 *   - name string|\Drupal\Core\StringTranslation\TranslatableMarkup The name
 *   of the group.
 *   - description string|\Drupal\Core\StringTranslation\TranslatableMarkup The
 *   description of the group. Tags should be keyed by the type and each type
 *   array is an array of tags with the following keys:
 *   - tag string|\Drupal\Core\StringTranslation\TranslatableMarkup The
 *   lowercase tag value.
 *   - description string|\Drupal\Core\StringTranslation\TranslatableMarkup A
 *   description of the tag, how it is used, examples, links, etc.
 */
function hook_field_tag_tags_info() {
  return [
    'types' => [
      'visibility' => [
        'name' => t('Visibility'),
        'description' => t('Affect the visibility and/or device visibility.'),
      ],
      'image' => [
        'name' => t('Image'),
        'description' => t('Field tags that apply to images.'),
      ],
    ],
    'tags' => [
      'layout' => [
        [
          'tag' => '1 of foo',
          'description' => t('Tags that follow this pattern define a group that is sequenced every page load; only one item is loaded per HTTP request.  You can see this at work on Sanctuaries of Silence (/node/9146), it\'s how we get a different VR experience to show up on each load.  The group `{id}` can be any arbitrary value, e.g. "vr", "foo", "bar".'),
        ],
        [
          'tag' => '#foo',
          'description' => t('Where <code>foo</code> is an HTML ID, e.g., <code>#downloads</code>.  See _Creating HTML Anchors Using Tags_.'),
        ],
      ],
      'language' => [
        [
          'tag' => 'english',
          'description' => t('Indicates the tagged item is in the English language.  Used for downloads and resources.'),
        ],
        [
          'tag' => 'spanish',
          'description' => t('Indicates the tagged item is in the Spanish language.  Used for downloads and resources.'),
        ],
      ],
    ],
  ];
}

/**
 * Allow extensions to provide tag usage rules for entities and fields.
 *
 * @return \Drupal\field_tag\Rule\Rule[]
 */
function hook_field_tag_validation_rules(): array {
  return [
    (new \Drupal\field_tag\Rule\Rule())
      ->condition(\Drupal\field_tag\Rule\Rule::TAG_VALUE, 'thumb')
      ->condition(\Drupal\field_tag\Rule\Rule::ENTITY, 'node')
      ->condition(\Drupal\field_tag\Rule\Rule::BUNDLE, [
        'page',
        'blog_entry',
      ], 'in')
      ->condition(\Drupal\field_tag\Rule\Rule::HAS_FIELD, 'field_photos')
      ->require(\Drupal\field_tag\Rule\Rule::TAG_MIN_PER_FIELD, 1),

    (new \Drupal\field_tag\Rule\Rule())
      ->condition(\Drupal\field_tag\Rule\Rule::TAG_REGEX, '/^(english|spanish|french)$/')
      ->require(\Drupal\field_tag\Rule\Rule::ENTITY, 'node')
      ->require(\Drupal\field_tag\Rule\Rule::TAGGED_FIELD, 'field_articles'),
  ];
}

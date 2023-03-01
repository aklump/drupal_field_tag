<?php

namespace Drupal\field_tag;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field_tag\Entity\FieldTag;
use Drupal\paragraphs\ParagraphInterface;
use InvalidArgumentException;
use RuntimeException;

/**
 * Service class supporting the use of field_tag module.
 */
class FieldTagService {

  /**
   * An service instance.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  protected $entity;

  /**
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * FieldTagService constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   A service instance.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * Return an array of FieldTag entities by parent entity from storage.
   *
   * @param \Drupal\Core\Entity\EntityInterface $parent
   *   The parent entity.
   * @param string|null $field_name
   *   Optionally, pass a field name to limit tags to that field only.
   *
   * @return \Drupal\field_tag\Entity\FieldTag[]
   *   An array of field_tag entities attached to $parent.  Keys are irrelevant.
   */
  public function getAllFieldTagsByParent(EntityInterface $parent, string $field_name = NULL): array {
    try {
      $storage = $this->entityTypeManager->getStorage('field_tag');
      $query = $storage
        ->getQuery()
        ->condition('deleted', 0)
        ->condition('parent_entity', $parent->getEntityTypeId())
        ->condition('parent_id', $parent->id());
      if ($field_name) {
        $query->condition('field_name', $field_name);
      }
      $ids = $query->execute();
      if (!empty($ids)) {
        return array_values($storage->loadMultiple($ids));
      }
    }
    catch (\Exception $exception) {
      watchdog_exception('field_tag', $exception);
    }

    return [];
  }

  /**
   * Attach any existing field_tags to a parent entity.
   *
   * This function sets the value of $item->fieldTag as an entity instance.
   * Don't confuse with $item->field_tag, which holds the value during entity
   * CRUD operations.
   *
   * If called a second time, nothing happens because
   * $entity->field_tag_attached is set to true when the field_tags are first
   * attached.  To force a reattachment you must apply
   * $entity->field_tag_attached = false before calling this method.
   *
   * If you want to have tags attached automatically to entities on load then
   * you should implement hook_entity_load and call this method as desired.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The parent entity to attach to.
   *
   * @return $this
   *   Self for chaining.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function attachTags(EntityInterface $entity) {
    // We have to set this because this method may be chained and followed by
    // ::getItemsTaggedBy.
    $this->entity = $entity;
    if (isset($this->entity->field_tag_attached)) {
      return $this;
    }
    $tags = $this->getAllFieldTagsByParent($this->entity);
    foreach ($tags as $tag) {
      $field_name = $tag->getFieldName();
      if ($this->entity->hasField($field_name)) {
        $delta = $tag->getDelta();
        if (($item = $this->entity->get($field_name)->get($delta))) {
          $item->fieldTag = $tag;
        }
      }
    }
    $this->entity->field_tag_attached = TRUE;

    return $this;
  }

  /**
   * Return the field item tagged by $tag if it exists.
   *
   * You must call ::attachTags first to set the entity.
   *
   * @param string $tag
   *   The tag to look for.
   * @param string $field_name
   *   The field name on, the $entity passed to :attachTags().
   *
   * @return \Drupal\Core\Field\FieldItemInterface[]
   *   The items in $field_name tagged by $tag or [].  The keys are going to
   *   match the item list delta.
   *
   * // TODO Consider passing in $entity and doing away with $this->entity; then the service can be shared.
   */
  public function getItemsTaggedBy(string $tag, string $field_name): array {
    if (is_null($this->entity)) {
      throw new RuntimeException("Missing $this->entity; did you call ::attachTags() first?");
    }
    if (empty($field_name)) {
      throw new InvalidArgumentException("\$field_name may not be empty.");
    }
    $items = [];

    // I'm allowing $tag to be empty without an exception because it feels more
    // appropriate, and we'll just quietly return no items.
    if (empty(trim($tag))) {
      return $items;
    }
    if ($this->entity->hasField($field_name)) {
      foreach ($this->entity->get($field_name) as $delta => $item) {

        // During entity inserts we will have ->field_tag, and that should be
        // used.  During entity updates we might have both, and we should assume
        // that the unsaved version is more correct, so we should also use
        // field_tag.  In effect, we should use 'field_tag' over using
        // 'fieldTag', when it's present.
        $value = $item->getValue();
        if (array_key_exists('field_tag', $value)) {

          // Keep this as a nested IF because we want to always use "field_tag"
          // if the key is present and NOT fieldTag in such cases.
          if (Tags::create($value['field_tag'])->has($tag)) {
            $items[$delta] = $item;
          }
        }
        elseif ($item->fieldTag && $item->fieldTag->hasTag($tag)) {
          $items[$delta] = $item;
        }
      }
    }

    return $items;
  }

  /**
   * Return the target_id and value for an unsaved field tag field item.
   *
   * @param \Drupal\Core\TypedData\TypedDataInterface|array $item
   *   The single item from a FieldItemList, or an array with the key
   *   'field_tag'.
   *
   * @return string|null
   *   If the item does not have field_tag as a key, meaning there is no CRUD
   *   indication, then NULL will be returned.  Otherwise a trimmed string
   *   value will be returned which is the tags value, possibly a CSV string.
   *   Any duplicated tags will be removed.  Commas not followed by a space
   *   will be replaced with ', '.
   */
  public function normalizeItemFieldTag(TypedDataInterface $item) {
    $item = $item->getValue();
    if (!array_key_exists('field_tag', $item)) {
      return NULL;
    }

    return implode(', ', Tags::create($item['field_tag'])
      ->all());
  }

  /**
   * Normalize a field tag CSV list using ", " (quotes excluded).
   *
   * @param string $value
   *   The field tag value, a CSV string.
   *
   * @return string
   *   The normalized string with duplicate tags removed; leading/trailing
   *   commas removed, and spaces inserted after each comma.
   *
   * @deprecated  Use the following instead:
   *
   * If you wish to have ", " separation then do this:
   * @code
   * $normalized_value = implode(', ', \Drupal\field_tag\Tags::create($value)->all());
   * @endcode
   *
   * Otherwise for CSV, do this:
   *
   * @code
   * $normalized_value = (string) \Drupal\field_tag\Tags::create($value);
   * @endcode
   */
  public function normalizeFieldTagValue(string $value) {
    return implode(', ', Tags::create($value)->all());
  }

  /**
   * Convert a field tag value to an array.
   *
   * @param string $value
   *   A field tag value (csv) string.
   *
   * @return array
   *   An array of unique tags as indicated by $value.
   *
   * @deprecated Use `\Drupal\field_tag\Tags::create($value)->all()` instead.
   */
  public function getFieldTagsAsArray(string $value): array {
    $value = explode(',', $value);
    $tags = new Tags(...$value);

    return $tags->all();
  }

  /**
   * Get the parent tags for a referenced paragraph.
   *
   * Because field tags for paragraphs are stored in the referencing parent
   * field and not in the paragraph entity, you may call this method with a
   * paragraph to get the field tags associated with it.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The paragraph entity, for which you want to get the tags that are
   *   attached to the parent's referencing field.
   *
   * @return \Drupal\field_tag\Entity\FieldTag[]
   *   An indexed array, with arbitrary keys. In the case that the parent
   *   references the same paragraph more than once on the same field, you will
   *   receive more than one result in the return array from this; this should
   *   be an edge case, so in most cases you should use $field_tags[0].
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getFieldTagsByParagraph(ParagraphInterface $paragraph): array {
    $parent = $paragraph->getParentEntity();
    $parent_field = $paragraph->parent_field_name->value ?? NULL;
    $target_id = $paragraph->id();
    $field_tags = [];
    foreach ($parent->{$parent_field} as $delta => $item) {
      if ($item->target_id == $target_id) {
        $field_tag = FieldTag::loadByParentField($parent, $parent_field, $delta);
        if ((string) $field_tag) {
          $field_tags[] = $field_tag;
        }
      }
    }

    return $field_tags;
  }

  /**
   * Determines if an entity_type/bundle has any fields with tags enabled.
   *
   * @param string $entity_type_id
   *   The entity type ID. Only entity types that implement
   *   \Drupal\Core\Entity\FieldableEntityInterface are supported.
   * @param string $bundle
   *   The bundle.
   *
   * @return bool
   *   True if any of the fields have field_tags enabled.
   */
  public function doesBundleUseFieldTags(string $entity_type_id, string $bundle) {
    $usages = &drupal_static(__METHOD__);
    $cid = "$entity_type_id.$bundle";
    if (!is_null($usages[$cid] ?? NULL)) {
      return $usages[$cid];
    }

    $usages[$cid] = FALSE;
    $field_definitions = $this->entityFieldManager
      ->getFieldDefinitions($entity_type_id, $bundle);
    foreach ($field_definitions as $field_definition) {
      if (!$field_definition instanceof FieldConfig) {
        continue;
      }
      $settings = $field_definition->getThirdPartySettings('field_tag');
      if ($settings['enabled'] ?? FALSE) {
        $usages[$cid] = TRUE;
        break;
      }
    }

    return $usages[$cid];
  }

  /**
   * Determines if a given entity instance has field tags on any fields.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   A single entity instance.
   *
   * @return bool
   *   True if any of the entity's fields have field_tags enabled.
   *
   * @deprecated Use \Drupal\field_tag\FieldTagService::getTaggedFieldDefinitionsByEntity() cast to boolean instead.
   */
  public function doesEntityUseFieldTags(EntityInterface $entity) {
    if ($entity instanceof FieldableEntityInterface) {
      return $this->doesBundleUseFieldTags($entity->getEntityTypeId(), $entity->bundle());
    }

    return FALSE;
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return \Drupal\field\FieldConfigInterface[]
   *   An indexed array of field definitions.
   */
  public function getTaggedFieldDefinitionsByEntity(EntityInterface $entity): array {
    if ('field_tag' === $entity->getEntityTypeId() || !$entity instanceof FieldableEntityInterface) {
      return [];
    }

    $cid = $entity->getEntityTypeId() . '.' . $entity->bundle();
    static $index;
    if (isset($index[$cid])) {
      return $index[$cid];
    }

    $field_definitions = $this->entityFieldManager
      ->getFieldDefinitions($entity->getEntityTypeId(), $entity->bundle());
    $index[$cid] = [];
    foreach ($field_definitions as $field_definition) {
      if (!$field_definition instanceof \Drupal\field\FieldConfigInterface) {
        continue;
      }
      $settings = $field_definition->getThirdPartySettings('field_tag');
      if ($settings['enabled'] ?? FALSE) {
        $index[$cid][] = $field_definition;
      }
    }

    return $index[$cid];
  }

}

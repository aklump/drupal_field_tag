<?php

namespace Drupal\field_tag;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\field_tag\Entity\FieldTag;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Service class supporting the use of field_tag module.
 */
class FieldTagService {

  /**
   * An instance of EntityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  protected $entity;

  /**
   * FieldTagService constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   An instance of EntityTypeManager.
   */
  public function __construct(EntityTypeManager $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Return an array of FieldTag entities by parent entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $parent
   *   The parent entity.
   * @param string|null $field_name
   *   Optionally, only for this field on $parent.
   *
   * @return array|\Drupal\Core\Entity\EntityInterface[]
   *   An array of field_tag entities attached to $parent.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getAllFieldTagsByParent(EntityInterface $parent, string $field_name = NULL) {
    $storage = $this->entityTypeManager
      ->getStorage('field_tag');
    $query = $storage
      ->getQuery()
      ->condition('parent_entity', $parent->getEntityTypeId())
      ->condition('parent_id', $parent->id());
    if ($field_name) {
      $query->condition('field_name', $field_name);
    }
    $ids = $query->execute();
    $entities = $ids ? $storage->loadMultiple($ids) : [];

    return $entities;
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
   * you should implement hook_entity_load and call this method as desired.a
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
      $field_name = $tag->get('field_name')->value;
      $delta = $tag->get('delta')->value;
      if (($item = $this->entity->get($field_name)->get($delta))) {
        $item->fieldTag = $tag;
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
   */
  public function getItemsTaggedBy(string $tag, string $field_name): array {
    if (is_null($this->entity)) {
      throw new \RuntimeException("Missing $this->entity; did you call ::attachTags() first?");
    }
    $items = [];
    if ($this->entity->hasField($field_name)) {
      foreach ($this->entity->get($field_name) as $delta => $item) {

        // During entity inserts we will have ->field_tag, and that should be
        // used.  During entity updates we might have both, and we should assume
        // that the unsaved version is more correct, so we should also use
        // field_tag.  In effect, we should use 'field_tag' over using
        // 'fieldTag', when it's present.
        if (array_key_exists('field_tag', $item->getValue())) {
          if (($tags = $item->field_tag)
            && FieldTag::create(['tag' => $tags])->hasTag($tag)) {
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
   *   In the case that the parent references the same paragraph more than once
   *   on the same field, you will receive more than one result in the return
   *   array from this; this should be an edge case, so in most cases
   *   array_first() should be used.
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

}

<?php

namespace Drupal\field_tag;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;

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
    $this->entity = $entity;
    if (empty($entity->field_tag_attached)) {
      $tags = $this->getAllFieldTagsByParent($entity);
      foreach ($tags as $tag) {
        $field_name = $tag->get('field_name')->value;
        $delta = $tag->get('delta')->value;
        if (($item = $entity->get($field_name)->get($delta))) {
          $item->field_tag = $tag;
        }
      }
      $entity->field_tag_attached = TRUE;
    }

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
   *   The items in $field_name tagged by $tag or [].
   */
  public function getItemsTaggedBy(string $tag, string $field_name): array {
    if (is_null($this->entity)) {
      throw new \RuntimeException("Missing $this->entity; did you call ::attachTags() first?");
    }
    $items = [];
    if ($this->entity->hasField($field_name)) {
      foreach ($this->entity->get($field_name) as $item) {
        if ($item->field_tag && $item->field_tag->hasTag($tag)) {
          $items[] = $item;
        }
      }
    }

    return $items;
  }

}

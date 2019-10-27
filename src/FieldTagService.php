<?php

namespace Drupal\field_tag;

use Drupal\Core\Entity\EntityInterface;

class FieldTagService {

  /**
   * Return an array of FieldTag entities by parent entity.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $parent
   *   The parent entity.
   * @param string|NULL $field_name
   *   Optionally, only for this field on $parent.
   *
   * @return array|\Drupal\Core\Entity\EntityInterface[]
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getAllFieldTagsByParent(EntityInterface $parent, string $field_name = NULL) {
    $storage = \Drupal::entityTypeManager()
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

}

<?php

namespace Drupal\field_tag\Plugin\migrate\source;

use Drupal\migrate_drupal\Plugin\migrate\source\ContentEntity;

/**
 * Source plugin to get content entities from the current version of Drupal.
 *
 * This plugin differs from "content_entity:node" in that this plugin attaches
 * field tags to source entities.  It should be used anytime field tags are
 * involved.
 *
 * @MigrateSource(
 *   id = "field_tag_content_entity",
 *   source_module = "field_tag",
 *   deriver =
 *   "\Drupal\migrate_drupal\Plugin\migrate\source\ContentEntityDeriver",
 * )
 */
class FieldTagContentEntity extends ContentEntity {

  /**
   * Loads and yields entities, one at a time.
   *
   * @param array $ids
   *   The entity IDs.
   *
   * @return \Generator
   *   An iterable of the loaded entities.
   */
  protected function yieldEntities(array $ids) {
    $storage = $this->entityTypeManager
      ->getStorage($this->entityType->id());
    $field_tag_service = \Drupal::service('field_tag');

    foreach ($ids as $id) {
      /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
      $entity = $storage->load($id);

      // We do not use the field tag service because we want the key
      // 'field_tags' which is going to be better for the toArray.
      $tags = $field_tag_service->getAllFieldTagsByParent($entity);
      foreach ($tags as $tag) {
        $field_name = $tag->get('field_name')->value;
        $delta = $tag->get('delta')->value;
        if (($item = $entity->get($field_name)->get($delta))) {
          $item->field_tag = (string) $tag;
        }
      }

      yield $this->toArray($entity);
      if ($this->configuration['include_translations']) {
        foreach ($entity->getTranslationLanguages(FALSE) as $language) {
          yield $this->toArray($entity->getTranslation($language->getId()));
        }
      }
    }
  }

}

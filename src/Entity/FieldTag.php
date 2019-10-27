<?php

namespace Drupal\field_tag\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the Field tag entity.
 *
 * @ingroup field_tag
 *
 * @ContentEntityType(
 *   id = "field_tag",
 *   label = @Translation("Field tag"),
 *   handlers = {
 *     "storage" = "Drupal\field_tag\FieldTagStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "access" = "Drupal\field_tag\FieldTagAccessControlHandler",
 *   },
 *   base_table = "field_tag",
 *   data_table = "field_tag_field_data",
 *   translatable = FALSE,
 *   render_cache = FALSE,
 *   admin_permission = "administer field tag entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *   },
 * )
 */
class FieldTag extends ContentEntityBase implements FieldTagInterface {

  use EntityChangedTrait;

  /**
   * Create a new instance from context.
   *
   * @param \Drupal\Core\Entity\EntityInterface $parent
   * @param string $field_name
   * @param int $delta
   *
   * @return \Drupal\Core\Entity\EntityInterface
   */
  public static function createFromParent(EntityInterface $parent, string $field_name, $delta = 0): EntityInterface {
    return static::create([
      'parent_entity' => $parent->getEntityTypeId(),
      'parent_id' => $parent->id(),
      'field_name' => $field_name,
      'delta' => $delta,
    ]);
  }

  /**
   * Attempt to load a FieldTag entity by context.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param string $field_name
   * @param int $delta
   *
   * @return EntityInterface|null
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function loadFromParent(EntityInterface $entity, string $field_name, $delta = 0) {
    $query = \Drupal::entityTypeManager()
      ->getStorage('field_tag')
      ->getQuery()
      ->condition('parent_entity', $entity->getEntityTypeId())
      ->condition('parent_id', $entity->id())
      ->condition('field_name', $field_name)
      ->condition('delta', $delta);
    $ids = $query->execute();

    if (count($ids) > 1) {
      throw new \RuntimeException("Too many instances exist in the entity table for parent entity.");
    }

    return $ids && ($id = array_first($ids)) ? static::load($id) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function getParentEntity() {
    return $this->entityTypeManager()
      ->getStorage($this->parent_entity->value)
      ->load($this->parent_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getTag(): string {
    return (string) $this->tag->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = [];
    if ($entity_type->hasKey('id')) {
      $fields[$entity_type->getKey('id')] = BaseFieldDefinition::create('integer')
        ->setLabel(new TranslatableMarkup('ID'))
        ->setReadOnly(TRUE)
        ->setSetting('unsigned', TRUE);
    }

    $fields['parent_entity'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Parent entity type'));

    $fields['parent_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('The id of the parent entity'));

    $fields['field_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Parent field name'));

    $fields['delta'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Parent field delta'));

    $fields['tag'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Tag'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return $this->getTag();
  }

}

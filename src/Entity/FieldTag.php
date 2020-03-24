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
 *     "storage" = "Drupal\Core\Entity\Sql\SqlContentEntityStorage",
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
   * Load FieldTag entity instance by parent entity/field/delta.
   *
   * If the field_tag entity does not exist then a new instance will be
   * returned with the $parent context already applied.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   This is the entity where the field exists to which the tag is attached.
   * @param string $field_name
   *   The name of the field the tag is attached to.
   * @param int $delta
   *   Optional item index, defaults to 0.
   *
   * @return \Drupal\field_tag\Entity\FieldTagInterface
   *   An existing (in the db) or new instance with supplied context.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function loadByParentField(EntityInterface $entity, string $field_name, $delta = 0): FieldTagInterface {
    // TODO Static cache optimize?
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

    if ($id = array_first($ids)) {
      return static::load($id);
    }

    return static::create([
      'parent_entity' => $entity->getEntityTypeId(),
      'parent_id' => $entity->id(),
      'field_name' => $field_name,
      'delta' => $delta,
    ]);
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
  public function getValue(): string {
    return trim($this->tag->value, ', ');
  }

  /**
   * {@inheritdoc}
   */
  public function getTags(): array {
    $value = $this->getValue();
    if (empty($value)) {
      return [];
    }

    return array_filter(array_map('trim', explode(',', $this->getValue() . ',')));
  }

  /**
   * {@inheritdoc}
   */
  public function hasTag(string $tag): bool {
    return in_array(strtolower($tag), array_map('strtolower', $this->getTags()));
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
    return (string) $this->tag->value;
  }

  /**
   * @inheritDoc
   */
  public function addTag(string $tag): FieldTagInterface {
    $tags = $this->getTags();
    $tags[] = $tag;
    $this->tag->value = implode(',', array_unique($tags));

    return $this;
  }

}

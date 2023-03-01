<?php

namespace Drupal\field_tag\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\field_tag\Tags;
use RuntimeException;

/**
 * Defines the Field tag entity.
 *
 * Create an entity by doing this:
 *
 * @code
 * $field_tag_entity = \Drupal\field_tag\Entity\FieldTag::createFromTags(\Drupal\field_tag\Tags::create('do', 're', 'mi'));
 * @endcode
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
 *     "label" = "tag",
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
  public static function loadByParentField(EntityInterface $entity, string $field_name, int $delta = 0): FieldTagInterface {
    try {
      // Field tags only exist in database AFTER the parent has been created.
      if (!$entity->isNew()) {

        // TODO Static cache optimize?
        $query = \Drupal::entityTypeManager()
          ->getStorage('field_tag')
          ->getQuery()
          ->condition('deleted', 0)
          ->condition('parent_entity', $entity->getEntityTypeId())
          ->condition('parent_id', $entity->id())
          ->condition('field_name', $field_name)
          ->condition('delta', $delta);
        $ids = $query->execute();

        if (count($ids) > 1) {
          throw new RuntimeException(sprintf('Too many instances (%d) for field: %s exist in the entity table for parent entity (%s %d).', count($ids), $field_name, $entity->getEntityTypeId(), $entity->id()));
        }

        $id = array_shift($ids);
        if ($id) {
          return static::load($id);
        }
      }
    }
    catch (\Exception $exception) {
      watchdog_exception('field_tag', $exception);
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
    return strval(Tags::create($this->tag->value));
  }

  /**
   * {@inheritdoc}
   */
  public function getTags(): array {
    return Tags::create($this->tag->value)->all();
  }

  /**
   * {@inheritdoc}
   */
  public function hasTag(string $tag, bool $use_regex = FALSE): bool {
    $tags = new Tags($this->tag->value);
    if ($use_regex) {
      return count($tags->match($tag)) > 0;
    }

    return $tags->has($tag);
  }

  /**
   * {@inheritdoc}
   */
  public function matchTags(string $regex): array {
    $matches = [];
    Tags::create($this->tag->value)->match($regex, $matches);

    return $matches;
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

    $fields['deleted'] = BaseFieldDefinition::create('boolean')
      ->setDefaultValue(FALSE)
      ->setLabel(t('Deleted'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return $this->getValue();
  }

  /**
   * Create a new entity from a Tags instance.
   *
   * @param \Drupal\field_tag\Tags $tags
   *
   * @return \Drupal\field_tag\Entity\FieldTag
   */
  public static function createFromTags(Tags $tags): FieldTag {
    return static::create(['tag' => strval($tags)]);
  }

  /**
   * @inheritDoc
   */
  public function addTag(string $tag): FieldTagInterface {
    $this->tag->value = strval(Tags::create($this->tag->value)->add($tag));

    return $this;
  }

  public function removeTag(string $tag): FieldTagInterface {
    $tags = new Tags($this->tag->value);
    $this->tag->value = (string) $tags->filter(function (string $item) use ($tag) {
      return $item !== $tag;
    });

    return $this;
  }

}

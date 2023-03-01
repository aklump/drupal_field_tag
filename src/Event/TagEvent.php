<?php

namespace Drupal\field_tag\Event;

use Drupal\Core\Entity\EntityInterface;
use Drupal\field_tag\Entity\FieldTagInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Defines the event for When a new field tag is added..
 */
class TagEvent extends Event {

  private $fieldTag;

  private $parent;

  public function __construct(FieldTagInterface $field_tag) {
    $this->fieldTag = $field_tag;
  }

  /**
   * @return \Drupal\field_tag\Entity\FieldTagInterface
   *   An instance that contains only those tags that were added to the entity,
   *   field, and delta.
   *
   * @code
   *   $only_tags_added = $event->getFieldTag()->all();
   *   $event->getFieldTag()->fieldName();
   *   $event->getFieldTag()->getParentEntity();
   *   $event->getFieldTag()->delta();
   * @endcode
   */
  public function getFieldTag(): FieldTagInterface {
    return $this->fieldTag;
  }

  /**
   * @return string
   *
   * @deprecated Use \Drupal\field_tag\Event\TagEvent::getFieldTag()->getValue().
   */
  public function getTag(): string {
    return $this->getFieldTag()->getValue();
  }

  /**
   * @return \Drupal\Core\Entity\EntityInterface
   *
   * @deprecated Use \Drupal\field_tag\Event\TagEvent::getFieldTag->getParentEntity().
   */
  public function getEntity(): EntityInterface {
    return $this->getFieldTag()->getParentEntity();
  }

}

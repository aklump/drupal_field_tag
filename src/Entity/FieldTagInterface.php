<?php

namespace Drupal\field_tag\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface for defining Field tag entities.
 *
 * @ingroup field_tag
 */
interface FieldTagInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * Gets the parent entity of the paragraph.
   *
   * Preserves language context with translated entities.
   *
   * @return \Drupal\Core\Entity\EntityInterface||null
   *   The parent entity.
   */
  public function getParentEntity();

  /**
   * Return the tag value.
   *
   * @return string
   *   The arbitrary tag value.
   */
  public function getValue(): string;

  /**
   * Return an array by splitting the value of the field_tag.
   *
   * @return array
   *   The split value of field_tag.
   */
  public function getTags(): array;

  /**
   * Determine if a tag exists in the current value.
   *
   * @param string $tag
   *   The tag value to search for in getTags().
   *
   * @return bool
   *   true if the tag exists.
   */
  public function hasTag(string $tag): bool;

}

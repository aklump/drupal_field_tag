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
   * Gets the parent entity of the field tag.
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
   * @param bool $use_regex
   *   Treat $tag as a regex expression to match.  Defaults false.
   *
   * @return bool
   *   true if the tag exists.
   */
  public function hasTag(string $tag, bool $use_regex = FALSE): bool;

  /**
   * Return all tags that match a given regex expression.
   *
   * @param string $regex
   *   The regex expression including delimiters/modifers, e.g. "/.+/i".
   *
   * @return array
   *   Any tags that match the expression $regex.
   */
  public function matchTags(string $regex): array;

  /**
   * Add a new tag if it doesn't already exist.
   *
   * @param string $tag
   *   A new tag to add to the existing list.
   *
   * @return $this
   *   Self for chaining.
   */
  public function addTag(string $tag): self;

}

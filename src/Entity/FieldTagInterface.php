<?php

namespace Drupal\field_tag\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityInterface;

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
  public function getParentEntity(): ?EntityInterface;

  public function getFieldName(): string;

  public function getDelta(): int;

  public function setDelta(int $delta): FieldTagInterface;

  /**
   * Return the referenced entity when available.
   *
   * This will only return an if the field is a entity reference field; that is to say it has `target_id` as a value key.
   *
   * @return ?\Drupal\Core\Entity\EntityInterface
   */
  public function getTargetEntity(): ?EntityInterface;

  /**
   * Return the tag value.
   *
   * @return string
   *   The arbitrary tag value.
   */
  public function getValue(): string;

  /**
   * @deprecated
   */
  public function getTags(): array;

  /**
   * Return an array by splitting the value of the field_tag.
   *
   * @return array
   *   The split value of field_tag.
   */
  public function all(): array;

  /**
   * Determine if a tag exists in the current value.
   *
   * @param string $tag
   *   The tag value to search for in all().
   * @param bool $use_regex
   *   Treat $tag as a regex expression to match.  Defaults false.
   *
   * @return bool
   *   true if the tag exists.
   */
  public function hasTag(string $tag, bool $use_regex = FALSE): bool;

  /**
   * Return preg_match results after applying a pattern against all tags.
   *
   * @param string $regex
   *   The regex expression including delimiters/modifiers, e.g. "/.+/i".
   *
   * @return array
   *   The grouped results from preg_match for all matches to $regex.  Keys are
   *   the tags and the values are the $matches from preg_match.
   *
   * @see \Drupal\field_tag\Entity\FieldTagInterface::hasTag('foo', TRUE)
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
  public function addTag(string $tag): FieldTagInterface;

  public function setValue(string $value): FieldTagInterface;

  public function removeTag(string $tag): FieldTagInterface;
}

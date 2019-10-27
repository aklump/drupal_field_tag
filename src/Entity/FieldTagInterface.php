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
  public function getTag(): string;

}

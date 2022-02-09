<?php

namespace Drupal\field_tag\Event;

/**
 * Contains all events thrown by Field Tag Removed.
 */
final class FieldTagEvents {

  /**
   * Occurs When a new field tag is added..
   *
   * @var string
   */
  const TAG_ADDED = 'field_tag.tag_added';

  /**
   * Occurs when a field tag is removed.
   *
   * @var string
   */
  const TAG_REMOVED = 'field_tag.tag_removed';

}

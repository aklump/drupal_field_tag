<?php

namespace Drupal\field_tag\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Field tag entities.
 * @todo
 */
class FieldTagViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}

<?php

namespace Drupal\field_tag\Event;

use Drupal\Core\Entity\EntityInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Defines the event for When a new field tag is added..
 */
class TagEvent extends Event {

  private $tag;

  private $entity;

  public function __construct(string $tag, EntityInterface $entity) {
    $this->tag = $tag;
    $this->entity = $entity;
  }

  /**
   * @return string
   */
  public function getTag(): string {
    return $this->tag;
  }

  /**
   * @return \Drupal\Core\Entity\EntityInterface
   */
  public function getEntity(): EntityInterface {
    return $this->entity;
  }

}

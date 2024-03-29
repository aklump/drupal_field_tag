<?php

namespace Drupal\Tests\field_tag\Integration\TestTraits;

use Drupal\Core\Entity\EntityInterface;
use Drupal\field_tag\Entity\FieldTagInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\field_tag\FieldStorageHandler;

trait FieldTagMockTrait {

  public function createFieldTagMock(EntityInterface $parent, string $field_name, $delta, string $tags, int $id = NULL) {
    static $default_id = 0;
    $field_tag = $this->createConfiguredMock(FieldTagInterface::class, [
      'id' => $id ?? ++$default_id,
      'getParentEntity' => $parent,
      'getFieldName' => $field_name,
      'getDelta' => $delta,
      'getValue' => $tags,
    ]);

    return $field_tag;
  }

  private function createFieldStorageHandlerMock(array $methods_to_mock) {
    $methods_to_mock[] = 'datumToEntity';
    $handler = $this->createPartialMock(FieldStorageHandler::class, array_unique($methods_to_mock));
    $handler->method('datumToEntity')
      ->willReturnCallback(function ($datum, FieldableEntityInterface $parent, string $field_name) {
        return $this->createFieldTagMock($parent, $field_name, $datum['delta'], (string) $datum['tags']);
      });

    return $handler;
  }
}

<?php

namespace Drupal\Tests\field_tag\Integration;

use Drupal\field_tag\Entity\FieldTag;

class FieldTagTest extends \PHPUnit\Framework\TestCase {

  use \Drupal\Tests\field_tag\Integration\TestTraits\FieldTagMockTrait;

  public function testJsonSerialize() {
    $field_tag = $this->createPartialMock(FieldTag::class, [
      'getParentEntity',
      'getDelta',
      'getValue',
      'id',
      'getFieldName',
    ]);
    $field_tag->method('getParentEntity')->willReturn(NULL);
    $field_tag->method('getDelta')->willReturn(0);
    $field_tag->method('getValue')->willReturn('foo,bar');
    $field_tag->method('id')->willReturn('123');
    $field_tag->method('getFieldName')->willReturn('field_lorem');
    $data = $field_tag->jsonSerialize();
    $this->assertSame([
      'delta' => 0,
      'value' => 'foo,bar',
      'id' => 123,
      'parent_type' => NULL,
      'parent_id' => NULL,
      'field_name' => 'field_lorem',
    ], $data);
  }

}

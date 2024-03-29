<?php

namespace Drupal\Tests\field_tag\Integration;

use Drupal\field_tag\Entity\FieldTag;
use Drupal\field_tag\Helpers\Dedupe;
use Drupal\paragraphs\ParagraphInterface;
use PHPUnit\Framework\TestCase;

final class DedupeTest extends TestCase {

  public function testInvokeRemovesAllButMostRecentFieldTag() {
    $basis = [
      'getParentEntity' => $this->createConfiguredMock(ParagraphInterface::class, [
        'getEntityTypeId' => 'paragraph',
        'id' => 123,
      ]),
      'getFieldName' => 'field_main',
      'getDelta' => 0,
      'getValue' => 'lorem, ipsum',
    ];

    $field_tags = [];
    $field_tags[] = $this->createConfiguredMock(FieldTag::class, ['id' => 10] + $basis);
    $field_tags[] = $this->createConfiguredMock(FieldTag::class, ['id' => 30] + $basis);
    $field_tags[] = $this->createConfiguredMock(FieldTag::class, ['id' => 20] + $basis);

    $obj = new Dedupe();
    $result = $obj($field_tags);
    $this->assertCount(1, $result);
    $this->assertSame(30, $result[0]->id());
  }

}

<?php

namespace Drupal\field_tag\Tests;

use Drupal\field_tag\Entity\FieldTagInterface;
use Drupal\field_tag\Helpers\ComparisonValue;
use Drupal\paragraphs\ParagraphInterface;
use PHPUnit\Framework\TestCase;

class ComparisonValueTest extends TestCase {

  public function testInvokeDoesntBreakWithoutParent() {
    $basis = [
      'getParentEntity' => NULL,
      'getFieldName' => 'field_main',
      'getDelta' => 0,
      'getValue' => 'lorem, ipsum',
    ];
    $field_tag = $this->createConfiguredMock(FieldTagInterface::class, $basis);
    $obj = new ComparisonValue();
    $a = $obj($field_tag);
    $this->assertIsString($obj($field_tag));
    $this->assertNotEmpty($a);
    $this->assertSame($a, $obj($field_tag));
  }

  public function testInvokeReturnsSameStringTwice() {
    $basis = [
      'getParentEntity' => $this->createConfiguredMock(ParagraphInterface::class, [
        'getEntityTypeId' => 'paragraph',
        'id' => 123,
      ]),
      'getFieldName' => 'field_main',
      'getDelta' => 0,
      'getValue' => 'lorem, ipsum',
    ];
    $field_tag = $this->createConfiguredMock(FieldTagInterface::class, $basis);
    $obj = new ComparisonValue();
    $a = $obj($field_tag);
    $this->assertIsString($obj($field_tag));
    $this->assertNotEmpty($a);
    $this->assertSame($a, $obj($field_tag));
  }

}

<?php

namespace Drupal\Tests\field_tag\Integration;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\field_tag\Helpers\CountTags;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Drupal\field_tag\Helpers\CountTags
 */
final class CountTagsTest extends TestCase {

  use \AKlump\PHPUnit\Framework\MockObject\MockDrupalEntityTrait;

  protected $counter;

  public function testEntityTaggedObjectWorksAsExpected() {
    $entity = $this->createEntityMock('node', 'page', [
      'field_alpha' => [
        ['field_tag' => 'alpha'],
        ['field_tag' => 'zulu'],
      ],
      'field_bravo' => [
        ['field_tag' => 'bravo'],
        ['field_tag' => 'alpha'],
        ['field_tag' => 'yankee'],
      ],
      'field_charlie' => [
        ['field_tag' => 'charlie'],
        ['field_tag' => 'bravo,alpha'],
        ['field_tag' => 'whiskey'],
      ],
    ]);

    $result = $this->counter->__invoke($entity);
    $this->assertCount(6, $result);
    $this->assertSame(3, $result['alpha']);
    $this->assertSame(2, $result['bravo']);
    $this->assertSame(1, $result['charlie']);
    $this->assertSame(1, $result['whiskey']);
    $this->assertSame(1, $result['yankee']);
    $this->assertSame(1, $result['zulu']);
  }

  public function testFieldItemListInterfaceTaggedObjectWorksAsExpected() {
    $field_item_list = $this->createFieldItemListMock([
      ['field_tag' => 'lorem'],
      ['field_tag' => 'lorem,ipsum'],
      ['field_tag' => 'dolar'],
    ]);
    $result = $this->counter->__invoke($field_item_list);
    $this->assertSame(1, $result['ipsum']);
    $this->assertSame(2, $result['lorem']);
    $this->assertSame(1, $result['dolar']);
  }

  public function testFieldItemTaggedObjectWorksAsExpected() {
    $field_item = $this->createConfiguredMock(FieldItemInterface::class, [
      'getValue' => ['field_tag' => 'lorem,ipsum,lorem'],
    ]);
    $result = $this->counter->__invoke($field_item);
    $this->assertSame(1, $result['ipsum']);
    $this->assertSame(1, $result['lorem']);
  }

  public function testMixedCaseIsSeenAsTheSameTag() {
    $field_item_list = $this->createFieldItemListMock([
      ['field_tag' => 'foo'],
      ['field_tag' => 'FOO'],
    ]);
    $result = $this->counter->__invoke($field_item_list);
    $this->assertSame(2, $result['foo']);
  }

  public function testKeysAreCastToLowerCase() {
    $field_item_list = $this->createFieldItemListMock([
      ['field_tag' => 'FOO'],
    ]);
    $result = $this->counter->__invoke($field_item_list);
    $this->assertArrayHasKey('foo', $result);
    $this->assertArrayNotHasKey('FOO', $result);
  }

  public function testDuplicationBetweenListItemsIsCounted() {
    $field_item_list = $this->createFieldItemListMock([
      ['field_tag' => 'ipsum'],
      ['field_tag' => 'ipsum'],
    ]);
    $result = $this->counter->__invoke($field_item_list);
    $this->assertSame(2, $result['ipsum']);
  }

  public function testDuplicationOnASingleListItemIsNotCounted() {
    $field_item_list = $this->createFieldItemListMock([
      ['field_tag' => 'ipsum,ipsum'],
    ]);
    $result = $this->counter->__invoke($field_item_list);
    $this->assertSame(1, $result['ipsum']);
  }

  public function testNoFieldTagsReturnsEmptyArray() {
    $field_item_list = $this->createFieldItemListMock([]);
    $result = $this->counter->__invoke($field_item_list);
    $this->assertSame([], $result);
  }

  public function testInvalidTaggedObjectThrows() {
    $this->expectException(\InvalidArgumentException::class);
    $this->counter->__invoke(new \stdClass());
  }

  public function setUp() {
    $this->counter = new CountTags();
  }

}


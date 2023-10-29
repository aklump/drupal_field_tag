<?php

namespace Drupal\Tests\field_tag\Integration;

use AKlump\Drupal\PHPUnit\Integration\Framework\MockObject\MockDrupalEntityTrait;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\field_tag\Entity\FieldTag;
use Drupal\field_tag\FieldTagService;
use PHPUnit\Framework\TestCase;

class FieldTagServiceTest extends TestCase {

  use MockDrupalEntityTrait;

  protected $service;

  protected $moduleHandler;

  public function testGetTaggableEntityTypeIdsReturnsArray() {
    $this->moduleHandler->method('moduleExists')
      ->willReturnCallback(function ($name) {
        return $name === 'field_tag_paragraphs';
      });
    $result = $this->service->getTaggableEntityTypeIds();
    $this->assertIsArray($result);
  }

  public function testNormalizeItemFieldTagValueReturnsExpectedCSVString() {
    $typed_data = $this->createConfiguredMock(TypedDataInterface::class, [
      'getValue' => [
        'field_tag' => '   ,   lorem, IPSUM,   dolar, sit,     ',
      ],
    ]);
    $result = $this->service->normalizeItemFieldTag($typed_data);
    $this->assertSame('lorem, IPSUM, dolar, sit', $result);
  }

  public function testGetItemsTaggedByThrowsIfFieldNameIsEmpty() {
    $entity = $this->createEntityMock('node', 'page', [
      'field_downloads' => [
        ['field_tag' => 'alpha'],
        ['field_tag' => 'bravo'],
      ],
    ]);
    $this->expectException(\InvalidArgumentException::class);
    $this->service->getItemsTaggedBy('bravo', '', $entity);
  }

  public function testGetItemsTaggedByThrowsIfNoEntity() {
    $this->expectException(\RuntimeException::class);
    $this->service->getItemsTaggedBy('bravo', 'field_downloads');
  }

  public function testGetItemsTaggedByReturnsEmptyIfEntityDoesntHaveField() {
    $entity = $this->createEntityMock('node', 'page', [
      'field_downloads' => [
        ['field_tag' => 'alpha'],
        ['field_tag' => 'bravo'],
      ],
    ]);
    $items = $this->service->getItemsTaggedBy('alpha', 'field_main', $entity);
    $this->assertSame([], $items);
  }

  public function testGetItemsTaggedByReturnsEmptyArrayIfEmptyTag() {
    $entity = $this->createEntityMock('node', 'page', [
      'field_downloads' => [
        ['field_tag' => 'alpha'],
        ['field_tag' => 'bravo'],
      ],
    ]);
    $items = $this->service->getItemsTaggedBy('', 'field_downloads', $entity);
    $this->assertSame([], $items);
  }

  public function testGetItemsTaggedByRetainsTheIndexDeltaAsKey() {
    $entity = $this->createEntityMock('node', 'page', [
      'field_downloads' => [
        ['field_tag' => 'alpha'],
        ['field_tag' => 'bravo'],
      ],
    ]);
    $items = $this->service->getItemsTaggedBy('bravo', 'field_downloads', $entity);
    $this->assertCount(1, $items);
    $this->assertArrayHasKey(1, $items);
  }

  public function testGetItemsTaggedByUsingLanguageExample() {
    $entity = $this->createEntityMock('node', 'page', [
      'field_downloads' => [
        ['field_tag' => 'english', 'value' => 'The world'],
        ['field_tag' => 'spanish', 'value' => 'El mundo'],
        ['field_tag' => 'norwegian', 'value' => 'Verda'],
      ],
    ]);
    $items = $this->service->getItemsTaggedBy('english', 'field_downloads', $entity);
    $this->assertCount(1, $items);
    $this->assertSame('The world', $items[0]->value);
    $items = $this->service->getItemsTaggedBy('spanish', 'field_downloads', $entity);
    $this->assertCount(1, $items);
    $this->assertSame('El mundo', $items[1]->value);
    $items = $this->service->getItemsTaggedBy('norwegian', 'field_downloads', $entity);
    $this->assertCount(1, $items);
    $this->assertSame('Verda', $items[2]->value);
  }

  public function testGetItemsTaggedByReturnsOnlyTaggedItemsInList() {
    $entity = $this->createEntityMock('node', 'page', [
      'field_main' => [
        ['field_tag' => 'lorem'],
        ['field_tag' => 'ipsum'],
        ['field_tag' => 'lorem'],
      ],
    ]);
    $items = $this->service->getItemsTaggedBy('lorem', 'field_main', $entity);
    $items = array_filter($items, function (FieldItemInterface $item) {
      return $item->field_tag === 'lorem';
    });
    $this->assertCount(2, $items);
  }

  public function testGetItemsTaggedByReturnsOnlyTaggedFieldItemsWhenTaggedByFieldTagObjects() {
    $entity = $this->createEntityMock('node', 'page', [
      'field_main' => [
        [
          'fieldTag' => $this->createConfiguredMock(FieldTag::class, [
            'hasTag' => TRUE,
          ]),
        ],
        [
          'fieldTag' => $this->createConfiguredMock(FieldTag::class, [
            'hasTag' => FALSE,
          ]),
        ],
        [
          'fieldTag' => $this->createConfiguredMock(FieldTag::class, [
            'hasTag' => TRUE,
          ]),
        ],
      ],
    ]);
    $items = $this->service->getItemsTaggedBy('lorem', 'field_main', $entity);
    $this->assertcount(2, $items);
    $this->assertArrayHasKey(0, $items);
    $this->assertArrayNotHasKey(1, $items);
    $this->assertArrayHasKey(2, $items);
  }

  public function setUp() {
    $this->entityTypeManager = $this->createConfiguredMock(EntityTypeManagerInterface::class, []);
    $this->entityFieldManager = $this->createConfiguredMock(EntityFieldManagerInterface::class, []);
    $this->moduleHandler = $this->createConfiguredMock(ModuleHandlerInterface::class, []);
    $this->service = new FieldTagService($this->entityTypeManager, $this->entityFieldManager, $this->moduleHandler);
  }

}

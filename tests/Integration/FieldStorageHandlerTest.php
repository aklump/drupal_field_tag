<?php

namespace Drupal\Tests\field_tag\Integration;

use AKlump\Drupal\PHPUnit\Integration\Framework\MockObject\MockDrupalEntityTrait;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\field_tag\FieldStorageHandler;
use Drupal\field_tag\FieldTagService;
use PHPUnit\Framework\TestCase;
use Drupal\Tests\field_tag\Integration\TestTraits\FieldTagMockTrait;

/**
 * @covers \Drupal\field_tag\FieldStorageHandler::getStorageActions
 * // TODO This method is not fully covered yet.
 */
final class FieldStorageHandlerTest extends TestCase {

  use MockDrupalEntityTrait;
  use FieldTagMockTrait;

  public function dataFortestInvokeProvider() {
    $tests = [];
    $tests[] = [
      2,
      [
        'field_tag' => 'survey',
        'target_id' => '6879',
      ],
      [
        'field_tag' => 'survey',
        'target_id' => '8571',
      ],
    ];
    $tests[] = [
      4,
      [
        'field_tag' => '',
        'target_id' => '6879',
      ],
      [
        'field_tag' => 'survey',
        'target_id' => '8571',
      ],
      [
        'field_tag' => 'no survey,foobar,baz',
        'target_id' => '4550',
      ],
      [
        'field_tag' => '',
        'target_id' => '6716',
      ],
    ];

    return $tests;
  }

  /**
   * @dataProvider dataFortestInvokeProvider
   */
  public function testInvoke(int $expected_count, ...$items) {
    $field_tag_service = $this->createMock(FieldTagService::class);
    $field_tag_service->method('getAllFieldTagsByParent')->willReturn([]);

    $handler = $this->createFieldStorageHandlerMock(['getTagsByEntity']);
    $handler->method('getTagsByEntity')->willReturn([]);

    $block_content = $this->createEntityMock('block_content', 'sections', [
      'field_sections' => $items,
    ]);

    $list = $block_content->get('field_sections');
    $result = $handler->getStorageActions($list);
    $this->assertCount($expected_count, $result);
  }

  public function testInvokeWithEmptyListReturnsEmptyArray() {
    $field_tag_service = $this->createMock(FieldTagService::class);
    $handler = new FieldStorageHandler($field_tag_service);
    $list = $this->createFieldItemListMock([]);
    $result = $handler->getStorageActions($list);
    $this->assertSame([], $result);
  }


}

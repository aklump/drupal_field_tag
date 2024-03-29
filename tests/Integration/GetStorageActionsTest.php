<?php

namespace Drupal\Tests\field_tag\Integration;

use AKlump\Drupal\PHPUnit\Integration\Framework\MockObject\MockDrupalEntityTrait;
use Drupal\field_tag\FieldTagService;
use PHPUnit\Framework\TestCase;
use Drupal\Tests\field_tag\Integration\TestTraits\FieldTagMockTrait;

class GetStorageActionsTest extends TestCase {

  use MockDrupalEntityTrait;
  use FieldTagMockTrait;

  public function dataFortestFooProvider() {
    $it = glob(__DIR__ . '/../test_case_data/active/*.json');
    foreach ($it as $item) {
      $datum = json_decode(file_get_contents($item), TRUE);

      $parent = $this->createEntityMock($datum['entity_type_id'], $datum['bundle'], [
        $datum['field_name'] => $datum['item_list_being_saved'],
      ]);
      $list = $parent->get($datum['field_name']);

      //      $db = array_map(fn($action) => $this->createFieldTagMock($parent, $datum['field_name'], $datum['fieldTag']['delta'], $datum['fieldTag']['value']), $datum['database_records']);

      $db = $datum['database_records'];
      $expected = array_map(function ($action) use ($parent, $datum) {
        return [
            'fieldTag' => $this->createFieldTagMock($parent, $datum['field_name'], $action['fieldTag']['delta'], $action['fieldTag']['value']),
          ] + $action;
      }, $datum['expected_actions']);

      $data[] = [
        $db,
        $list,
        $expected,
      ];
    }

    return $data;
  }

  /**
   * @dataProvider dataFortestFooProvider
   */
  public function testFoo($db, \Drupal\Core\Field\FieldItemListInterface $items, $expected) {
    $field_tag_service = $this->createMock(FieldTagService::class);
    $field_tag_service->method('getAllFieldTagsByParent')->willReturn([]);

    // TODO Move to data provider class?
    $stored_tags = array_map(function ($item) {
      $parent = $this->createEntityMock($item['parent_entity'], '', [
        $item['field_name'] => [],
      ]);

      return $this->createFieldTagMock($parent, $item['field_name'], $item['delta'], $item['tag']);
    }, $db);

    $handler = $this->createFieldStorageHandlerMock(['getTagsByEntity']);
    $handler->method('getTagsByEntity')->willReturn($stored_tags);

    //    $parent = $items->getParent()
    //    $block_content = $this->createEntityMock('block_content', 'sections', [
    //      'field_sections' => $items,
    //    ]);
    //    $list = $block_content->get('field_sections');
    $actions = $handler->getStorageActions($items);

    $get_comp_value = fn($item) => [
      $item['action'],
      $item['fieldTag']->getValue(),
    ];
    $actions = array_map($get_comp_value, $actions);
    $expected = array_map($get_comp_value, $expected);

    $this->assertSame($expected, $actions);
  }

}


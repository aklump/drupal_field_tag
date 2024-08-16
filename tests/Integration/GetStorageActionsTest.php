<?php

namespace Drupal\Tests\field_tag\Integration;

use AKlump\Drupal\PHPUnit\Integration\Framework\MockObject\MockDrupalEntityTrait;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\field_tag\FieldTagService;
use PHPUnit\Framework\TestCase;
use Drupal\Tests\field_tag\Integration\TestTraits\FieldTagMockTrait;

class GetStorageActionsTest extends TestCase {

  use MockDrupalEntityTrait;
  use FieldTagMockTrait;

  const TEST_DATA_DIR = __DIR__ . '/../test_case_data/active/*.json';

  public function dataFortestFooProvider() {
    $data = [];
    $it = glob(self::TEST_DATA_DIR);
    if (empty($it)) {
      return [[NULL, NULL, NULL]];
    }
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
  public function testAssertTestDataWorksAsExpected($db, $items, $expected) {

    // This will happen if there is no test data in the directory.  The test
    // data is for developing and troubleshooting, as is this test, so in
    // general this test will not do anything long term.
    if (NULL === $db) {
      $this->assertTrue(TRUE, sprintf('No test data at %s, this test passes.', self::TEST_DATA_DIR));

      return;
    }

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


<?php


namespace Drupal\field_tag\Tests;

use Drupal\field_tag\Event\FieldTagEvents;
use Drupal\field_tag\FieldStorageHandler;
use PHPUnit\Framework\TestCase;
use Drupal\field_tag\Tags;

/**
 * @group extensions
 * @group field_tag
 * @covers \Drupal\field_tag\Tags
 */
final class FieldStorageHandlerTest extends TestCase {

  /**
   * Provides data for testDetermineCorrectAction.
   */
  public function dataForTestDetermineCorrectActionProvider() {
    $tests = [];

    // This demonstrates what happens if an item is removed.
    $tests[] = [
      FieldStorageHandler::ACTION_DELETE,
      'events' => [
        FieldTagEvents::TAG_REMOVED => Tags::create('alpha'),
      ],
      'in_storage' => [
        'tags' => new Tags('alpha'),
        'delta' => '0',
        'target_id' => '',
      ],
      'in_memory' => NULL,
    ];

    // This demonstrates what happens if the memory value is empty on a
    // previously stored field tag.
    $tests[] = [
      FieldStorageHandler::ACTION_DELETE,
      'events' => [
        FieldTagEvents::TAG_REMOVED => Tags::create('alpha'),
      ],
      'in_storage' => [
        'tags' => new Tags('alpha'),
        'delta' => '0',
        'target_id' => '',
      ],
      'in_memory' => [
        'tags' => new Tags(),
        'delta' => '0',
        'target_id' => '',
      ],
    ];

    // This demonstrates what happens if the field entity has not yet been
    // stored and the user adds a tag.
    $tests[] = [
      FieldStorageHandler::ACTION_SAVE,
      'events' => [
        FieldTagEvents::TAG_ADDED => Tags::create('alpha'),
      ],
      'in_storage' => NULL,
      'in_memory' => [
        'tags' => new Tags('alpha'),
        'delta' => '0',
        'target_id' => '',
      ],
    ];


    // This demonstrates what happens if the field entity has not yet been
    // stored and the user adds a tag.
    $tests[] = [
      FieldStorageHandler::ACTION_SAVE,
      'events' => [
        FieldTagEvents::TAG_ADDED => Tags::create('bravo,charlie'),
        FieldTagEvents::TAG_REMOVED => Tags::create('lorem,ipsum'),
      ],
      'in_storage' => [
        'tags' => new Tags('alpha, lorem,ipsum'),
        'delta' => '0',
        'target_id' => '',
      ],
      'in_memory' => [
        'tags' => new Tags('alpha, bravo, charlie'),
        'delta' => '0',
        'target_id' => '',
      ],
    ];

    // This demonstrates when the order of items in an ENTITY REFERENCE list
    // change, without any change to the tags.
    $tests[] = [
      FieldStorageHandler::ACTION_SAVE,
      'events' => [],
      'in_storage' => [
        'tags' => new Tags('lorem'),
        'delta' => '0',
        'target_id' => '12345',
      ],
      'in_memory' => [
        'tags' => new Tags('lorem'),
        'delta' => '1',
        'target_id' => '12345',
      ],
    ];

    // This demonstrates when the order of items in a list change, without any
    // change to the tags.
    $tests[] = [
      FieldStorageHandler::ACTION_SAVE,
      'events' => [],
      'in_storage' => [
        'tags' => new Tags('lorem'),
        'delta' => '0',
        'target_id' => '',
      ],
      'in_memory' => [
        'tags' => new Tags('lorem'),
        'delta' => '1',
        'target_id' => '',
      ],
    ];

    // This demonstrates if a tag is added to on an existing item.
    $tests[] = [
      FieldStorageHandler::ACTION_SAVE,
      'events' => [
        FieldTagEvents::TAG_ADDED => Tags::create('ipsum'),
      ],
      'in_storage' => [
        'tags' => new Tags('lorem'),
        'delta' => '0',
        'target_id' => '',
      ],
      'in_memory' => [
        'tags' => new Tags('lorem, ipsum'),
        'delta' => '0',
        'target_id' => '',
      ],
    ];

    // Even if the target ID changes, the storage action should be NONE because
    // the target ID is NOT stored in the database, only the delta.
    $tests[] = [
      FieldStorageHandler::ACTION_NONE,
      'events' => [],
      'in_storage' => [
        'tags' => new Tags('alpha'),
        'delta' => '0',
        'target_id' => '123',
      ],
      'in_memory' => [
        'tags' => new Tags('alpha'),
        'delta' => '0',
        'target_id' => '456',
      ],
    ];

    // This demonstrates how the user simply saves an unchanged entity.
    $tests[] = [
      FieldStorageHandler::ACTION_NONE,
      'events' => [],
      'in_storage' => [
        'tags' => new Tags('alpha'),
        'delta' => '0',
        'target_id' => '',
      ],
      'in_memory' => [
        'tags' => new Tags('alpha'),
        'delta' => '0',
        'target_id' => '',
      ],
    ];

    return $tests;
  }

  /**
   * @dataProvider dataForTestDetermineCorrectActionProvider
   */
  public function testDetermineCorrectAction(int $expected_action, array $expected_events, ?array $in_storage, ?array $in_memory) {
    $result = Testable::_determineCorrectAction($in_storage, $in_memory);

    $this->assertSame($expected_action, $result['action']);

    $fail_message = sprintf("IN_STORAGE: %s\n IN_MEMORY: %s\n", $in_storage['tags'] ?? '""', $in_memory['tags'] ?? '""');
    $this->assertEquals($expected_events, $result['events'], $fail_message);
  }
}

class Testable extends FieldStorageHandler {

  public static function _determineCorrectAction(?array $in_storage, ?array $in_memory) {
    return static::determineCorrectAction($in_storage, $in_memory);
  }
}

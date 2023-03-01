<?php

namespace Drupal\field_tag;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\field_tag\Entity\FieldTag;
use Drupal\field_tag\Event\FieldTagEvents;

class FieldStorageHandler {


  const ACTION_NONE = 0;

  const ACTION_SAVE = 1;

  const ACTION_DELETE = 2;

  /** @var \Drupal\field_tag\FieldTagService */
  private $fieldTagService;

  public function __construct() {
    $this->fieldTagService = \Drupal::service('field_tag');
  }

  /**
   * Get the appropriate storage actions for a this entity/field.
   *
   * @return array
   *   An array of actions to be taken with the keys:
   *   - 'action' int
   *   - 'fieldTag' \Drupal\field_tag\Entity\FieldTagInterface
   *   - 'events' \Drupal\field_tag\Tags[] Keyed by event type.
   *
   * @see \field_tag_entity_update()
   * @see \Drupal\field_tag\FieldStorageHandler::ACTION_NONE
   * @see \Drupal\field_tag\FieldStorageHandler::ACTION_SAVE
   * @see \Drupal\field_tag\FieldStorageHandler::ACTION_DELETE
   * @see \Drupal\field_tag\Event\FieldTagEvents::TAG_REMOVED
   * @see \Drupal\field_tag\Event\FieldTagEvents::TAG_ADDED
   */
  public function getStorageActions(FieldItemListInterface $list): array {
    $this->list = $list;
    $parent = $this->list->getEntity();
    $field_name = $this->list->getFieldDefinition()->getName();

    $stored_field_tags = $this->fieldTagService->getAllFieldTagsByParent($parent, $field_name);
    $in_storage = array_map(function (FieldTag $field_tag) {
      $target_entity = $field_tag->getTargetEntity() ?? NULL;

      return [
        [
          'delta' => $field_tag->delta->value,
          'target_id' => $target_entity ? $target_entity->id() : NULL,
          'tags' => Tags::create($field_tag->getValue()),
        ],
        $field_tag,
      ];
    }, $stored_field_tags);

    $in_memory = [];
    foreach ($this->list as $item) {
      $in_memory[$item->getName()] = [
        'delta' => $item->getName(),
        'target_id' => $item->target_id,
        'tags' => Tags::create($item->field_tag ?? strval($item->fieldTag ?? '')),
      ];
    }

    $result = [];
    foreach ($in_storage as $datum) {
      list($stored, $field_tag_entity) = $datum;

      if (!$field_tag_entity) {
        $field_tag_entity = FieldTag::createFromTags($stored['tags'], $parent, $field_name);
      }

      $memory_datum = $in_memory[$stored['delta']] ?? NULL;
      $correct_action = $this->determineCorrectAction($stored, $memory_datum);
      $correct_action['fieldTag'] = $field_tag_entity;

      // Alter the entity-to-be-saved to match memory data.
      if ($memory_datum && self::ACTION_SAVE === $correct_action['action']) {
        $field_tag_entity
          ->setDelta($memory_datum['delta'])
          ->setValue(strval($memory_datum['tags']));
      }
      $result[] = $correct_action;
    }

    // If there are more items in memory then these are field tags that have not
    // yet been inserted into the database.
    if (count($in_memory) > count($in_storage)) {
      $only_in_memory = array_slice($in_memory, count($in_storage));
      foreach ($only_in_memory as $datum) {
        $correct_action = $this->determineCorrectAction(NULL, $datum);
        $correct_action['fieldTag'] = FieldTag::createFromTags($datum['tags'], $parent, $field_name, $datum['delta']);
        $result[] = $correct_action;
      }
    }

    return $result;
  }

  /**
   * Note: I made this protected so I can unit test it.
   *
   * @param array|null $in_storage
   * @param array|null $in_memory
   *
   * @return array
   */
  protected static function determineCorrectAction(?array $in_storage, ?array $in_memory): array {
    $action = [
      'action' => self::ACTION_NONE,
      'events' => [],
    ];

    // If not in memory then delete, and we're done.
    if (is_null($in_memory) || !strval($in_memory['tags'])) {
      $action['action'] = self::ACTION_DELETE;
    }
    else {

      // If the storage and memory don't match then the field_tag entity storage
      // must be updated to reflect memory changes ... however we don't yet know
      // if an event should be fired or not... stay tuned...
      $serialize = function (?array $data) {
        if (is_null($data)) {
          return '';
        }

        return implode('|', [
          $data['delta'],
          //        $data['target_id'],
          strval($data['tags']),
        ]);
      };
      $a = $serialize($in_storage);
      $b = $serialize($in_memory);
      if ($a != $b) {
        $action['action'] = self::ACTION_SAVE;
      }
    }

    if (!isset($in_memory['tags'])) {
      $in_memory['tags'] = new Tags();
    }
    if (!isset($in_storage['tags'])) {
      $in_storage['tags'] = new Tags();
    }

    // ... determine if tags were added or removed for possible events...
    $unique_tags_in_storage = $in_storage['tags']->diff($in_memory['tags']);
    $unique_tags_in_memory = $in_memory['tags']->diff($in_storage['tags']);

    // ...if any tags changed there may be a need to fire an event...
    if ($unique_tags_in_memory->count()) {
      $action['events'][FieldTagEvents::TAG_ADDED] = $unique_tags_in_memory;
    }
    if ($unique_tags_in_storage->count()) {
      $action['events'][FieldTagEvents::TAG_REMOVED] = $unique_tags_in_storage;
    }
    //    if ($unique_tags_in_memory->count() || $unique_tags_in_storage->count()) {
    //
    //
    //
    //      // Compare using target id...
    //      if (!empty($in_storage['target_id']) && !empty($in_memory['target_id'])) {
    //      }
    //
    //      // ... or delta, if not target_id
    //      else {
    //
    //      }
    //    }


    return $action;
  }

}

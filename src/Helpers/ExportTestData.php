<?php

namespace Drupal\field_tag\Helpers;

use Drupal;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\File\FileSystemInterface;

class ExportTestData {

  /**
   * @param \Drupal\Core\Field\FieldItemListInterface $list
   * @param array $pending_actions
   *
   * @return string
   *   JSON string of test data.
   */
  public function __invoke(FieldItemListInterface $list, array $pending_actions): string {
    $delta = 0;
    $item_list_being_saved = array_map(function ($item) use (&$delta) {
      unset($item['subform']);
      unset($item['top']);
      unset($item['target_id']);
      unset($item['target_revision_id']);

      return ['delta' => $delta++] + $item;
    }, $list->getValue());

    $parent = $list->getEntity();
    $field_name = $list->getFieldDefinition()->getName();

    $stored_field_tags = Drupal::service('field_tag')
      ->getAllFieldTagsByParent($parent, $field_name);
    $stored_field_tags = array_map(function ($item) {
      $item = json_decode(json_encode($item), TRUE);
      unset($item['parent_type']);
      unset($item['parent_id']);
      unset($item['field_name']);

      return $item;
    }, $stored_field_tags);

    $json = json_encode([
      'entity_id' => is_numeric($parent->id()) ? $parent->id() * 1 : $parent->id(),
      'entity_type_id' => $parent->getEntityTypeId(),
      'bundle' => $parent->bundle(),
      'field_name' => $field_name,
      'database_records' => $stored_field_tags,
      'item_list_being_saved' => $item_list_being_saved,
      'expected_actions' => $pending_actions,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    $dirname = DRUPAL_ROOT . '/' . Drupal::service('extension.list.module')
        ->getPath('field_tag') . '/tests/test_case_data/';
    Drupal::service('file_system')
      ->prepareDirectory($dirname, FileSystemInterface::CREATE_DIRECTORY);
    $filename = md5($json) . '.json';
    file_put_contents("$dirname/$filename", $json);

    return $json;
  }
}

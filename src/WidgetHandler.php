<?php

namespace Drupal\field_tag;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field_tag\Entity\FieldTag;
use Drupal\file\Plugin\Field\FieldType\FileItem;

final class WidgetHandler {

  /**
   * @var \Drupal\Core\Field\FieldItemListInterface
   */
  private $itemList;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $account;

  /**
   * @var \Drupal\Core\Entity\EntityInterface|NULL
   */
  private $entity;

  /**
   * @var string
   */
  private $fieldName;

  public function __construct(FormStateInterface $form_state, array $context, AccountInterface $account) {
    $this->formState = $form_state;
    $this->itemList = $context['items'];
    $this->account = $account;
    $this->entity = $context['items']->getParent();
    if ($this->entity) {
      $this->entity = $context['items']->getParent()->getEntity();
    }
    $field_definition = $this->itemList->getFieldDefinition();
    $this->fieldName = $field_definition->getName();
  }

  private function getSubmittedValues(): array {
    if (!$this->formState->isSubmitted()) {
      return [];
    }
    $field_name = $this->itemList->getFieldDefinition()->getName();

    $values = $this->formState->getValue($field_name, []);
    $values = array_filter(array_map(function ($item) {
      if (is_array($item) && isset($item['field_tag'])) {
        return $item['field_tag'];
      }

      return NULL;
    }, $values));

    return $values;
  }

  public function getValues(): array {
    $highest = $this->getHighestDelta();
    $values = $this->getStoredValues();
    $expected_size = $highest + 1;
    foreach ($this->itemList as $item) {
      $delta = $item->getName();
      if (isset($item->field_tag)) {

        // For the widget we want to have ", " separation.
        $values[$delta] = implode(', ', Tags::create($item->field_tag)->all());
      }
    }

    $submitted_values = $this->getSubmittedValues();
    if ($submitted_values) {
      $values = $submitted_values;
    }

    // We may need to have empty values for untagged items to reach our expected
    // size, if so fill those with ''.
    $values += array_fill(0, $expected_size, '');

    // Make sure we don't return values for items that no longer exist.
    $count = count($values);
    if ($count > $expected_size) {
      $values = array_slice($values, 0, $expected_size);
    }

    return $values;
  }

  /**
   * @return array
   *   All stored values keyed by delta.
   */
  private function getStoredValues(): array {
    $stored_values = [];
    try {
      foreach ($this->itemList as $item) {
        $delta = $item->getName();
        $stored_values[$delta] = FieldTag::loadByParentField($this->entity, $this->fieldName, $delta)
          ->getValue();
      }
    }
    catch (\Exception $exception) {
      watchdog_exception('field_tag', $exception);
    }

    return $stored_values;
  }

  private function getHighestDelta() {
    $deltas = [0];
    foreach ($this->itemList as $item) {
      $delta = $item->getName();
      if (is_numeric($delta) && $this->showFieldTagInputByFieldItem($item)) {
        $deltas[] = $delta;
      }
    }

    return max($deltas);
  }

  /**
   * Determine if a field item should be tagged when it's empty.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *
   *
   * @return bool
   */
  private function showFieldTagInputByFieldItem(FieldItemInterface $item): bool {
    // Empty file items are waiting for an upload, the UI looks cluttering when
    // the tagging input is there as well, therefore I will suppress them in
    // that state.
    if ($item instanceof FileItem) {
      // Note: at the time of writing FileItem::isEmpty() did not work
      // correctly, so I have to do a check here to really see if it's an empty
      // FileItem or not.
      if (empty($item->fids)) {
        return FALSE;
      }

      return TRUE;
    }

    return TRUE;

    return !$item->isEmpty();
  }

  public function getSettings(): array {
    return $this->itemList->getFieldDefinition()
      ->getThirdPartySettings('field_tag');
  }

  public function access(): AccessResultInterface {
    $field_definition = $this->itemList->getFieldDefinition();
    if (!$field_definition instanceof FieldConfig) {
      return AccessResult::forbidden();
    }
    $settings = $field_definition->getThirdPartySettings('field_tag');
    if (empty($settings['enabled'])) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowedIfHasPermission($this->account, 'access field tag inputs');
  }

}

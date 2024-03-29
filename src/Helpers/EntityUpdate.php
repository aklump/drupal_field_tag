<?php

namespace Drupal\field_tag\Helpers;

use Drupal;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\field\FieldConfigInterface;
use Drupal\field_tag\Event\TagEvent;
use Drupal\field_tag\FieldStorageHandler;

/**
 * Hook entity update handler.
 *
 * @see \field_tag_entity_update()
 */
class EntityUpdate {

  /**
   * @var \Drupal\field_tag\FieldTagService
   */
  private $fieldTagService;

  /**
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  private $dispatcher;

  /**
   * @var \Drupal\field_tag\FieldStorageHandler
   */
  private $storageHandler;

  public function __construct() {
    $this->fieldTagService = Drupal::service('field_tag');
    $this->dispatcher = Drupal::service('event_dispatcher');
    $this->storageHandler = new FieldStorageHandler($this->fieldTagService);
  }

  public function __invoke(EntityInterface $entity) {
    if (!$this->shouldProcessEntity($entity)) {
      return;
    }
    $fields = $this->fieldTagService->getTaggedFieldDefinitionsByEntity($entity);
    foreach ($fields as $field) {
      $this->handleSingleField($entity, $field);
    }
  }

  private function shouldProcessEntity(EntityInterface $entity): bool {
    if ('field_tag' === $entity->getEntityTypeId()) {
      return FALSE;
    }
    if (!in_array($entity->getEntityTypeId(), $this->fieldTagService->getTaggableEntityTypeIds())) {
      return FALSE;
    }
    $fields = $this->fieldTagService->getTaggedFieldDefinitionsByEntity($entity);
    if (count($fields) === 0) {
      return FALSE;
    }

    return TRUE;
  }

  private function handleSingleField(EntityInterface $parent, FieldConfigInterface $field) {
    $list = $parent->get($field->getName());
    if (!$list instanceof FieldItemListInterface) {
      return;
    }
    $pending_actions = $this->storageHandler->getStorageActions($list);

    /**
     * Keep this here as it can be used to generate test cases.
     */
//    $export = (new ExportTestData())($list, $pending_actions);

    foreach ($pending_actions as $pending_action) {
      $this->doAction($pending_action);
    }
    // fieldTag instances get detached from the parent entities during the CRUD
    // operations.  They will not always be present, but this will ensure
    // normalization.
    // @see \Drupal\field_tag\FieldTagService::attachTags()
    $this->fieldTagService->attachTags($parent);
    foreach ($list as $item) {
      unset($item->fieldTag);
    }
    unset($parent->field_tag_attached);
  }


  private function doAction(array $pending_action): void {
    switch ($pending_action['action']) {
      case FieldStorageHandler::ACTION_DELETE:
        $pending_action['fieldTag']->delete();
        break;

      case FieldStorageHandler::ACTION_SAVE:
        $pending_action['fieldTag']->save();
        break;
    }

    foreach ($pending_action['events'] as $type => $tags) {
      $context = clone $pending_action['fieldTag'];
      $context->setValue($tags);
      $this->dispatchEvent($type, new TagEvent($context));
    }
  }

  private function dispatchEvent($type, $event) {
    $version = class_exists('\Drupal') ? Drupal::VERSION : NULL;
    $version = $version ?? (defined('VERSION') ? constant('VERSION') : NULL);
    // https://www.drupal.org/node/3159012
    if (version_compare($version, 9) < 0) {
      $this->dispatcher->dispatch($type, $event);
    }
    else {
      $this->dispatcher->dispatch($event, $type);
    }
  }

}

<?php

namespace Drupal\field_tag_paragraphs;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\field\FieldConfigInterface;
use Drupal\field_tag\Entity\FieldTag;
use Drupal\field_tag\FieldTagService;

/**
 * Provide functionality to the field_tag_paragraphs module.
 */
class FieldTagParagraphs {

  use StringTranslationTrait;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\field_tag\FieldTagService
   */
  protected $fieldTagService;

  /**
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\field_tag\FieldTagService $field_tag_service
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    FieldTagService $field_tag_service,
    MessengerInterface $messenger,
    EntityTypeBundleInfoInterface $entity_type_bundle_info,
    ModuleHandlerInterface $module_handler
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->fieldTagService = $field_tag_service;
    $this->messenger = $messenger;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->moduleHandler = $module_handler;
  }

  /**
   * Look in field on entity for any tags that say to create paragraphs.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The parent entity to process.
   * @param \Drupal\field\FieldConfigInterface $field_config
   *   The field on $entity to look through for action tags.
   */
  public function processEntityField(EntityInterface $entity, FieldConfigInterface $field_config) {
    $field_name = $field_config->getName();
    $items = $entity->get($field_name);
    if (empty($items)) {
      return;
    }

    foreach ($items as $delta => $item) {
      $tags = $item->field_tag ?? $this->fieldTagService->normalizeItemFieldTag($item);
      if (empty($tags)) {
        continue;
      }

      $tags = FieldTag::create(['tag' => $tags]);

      // TODO Expose this pattern via settings.
      $matches = $tags->matchTags('/^add (.+) (before|after)$/i');
      if (empty($matches)) {
        continue;
      }
      try {
        foreach ($matches as $match) {
          list($original_tag, $subject, $location) = $match;
          $location = strtolower($location);

          // Look for Emmet-like syntax, e.g. "add foo*3 after".
          $subject = preg_replace_callback('/^(.+)(?:\*(\d))$/', function ($multiplication) {
            return implode('+', array_fill(0, $multiplication[2], $multiplication[1]));
          }, $subject);

          // Explode Emmet-like syntax, e.g. "add foo+bar+baz after".
          $bundles = explode('+', $subject);
          if ('before' === $location) {
            $bundles = array_reverse($bundles);
          }

          // Process each bundle.
          foreach ($bundles as $bundle) {
            $this->processActionTag($original_tag, $bundle, $location, $items, $delta);
          }
        }
      }
      catch (\Exception $exception) {
        watchdog_exception('field_tag_paragraphs', $exception);
        if (!$exception instanceof \InvalidArgumentException) {
          $this->messenger->addError($this->t('Processing field tag actions has failed due to an unknown reason.'));
        }
      }
    }
  }

  protected function processActionTag(string $original_tag, string $bundle, string $location, EntityReferenceFieldItemList $items, int $delta) {

    $this->moduleHandler->alter('field_tag_paragraphs_bundle', $bundle, $original_tag);

    // Validate the bundle.
    $target_bundle = $this->entityTypeBundleInfo->getAllBundleInfo()['paragraph'][$bundle] ?? NULL;
    if (!$target_bundle) {
      $this->messenger->addError($this->t('The command %command references an invalid paragraph bundle: %bundle.', [
        '%command' => $original_tag,
        '%bundle' => $bundle,
      ]));
      throw new \InvalidArgumentException(sprintf('Invalid paragraph bundle: %s', $bundle));
    }

    // Create the paragraph
    $paragraph = $this->entityTypeManager
      ->getStorage('paragraph')
      ->create([
        'type' => $bundle,
        // This can be read in hook_entity_presave to augment the paragraph with
        // additional fields at the custom level.
        '_field_tag_paragraphs' => ['field_tag' => $original_tag],
      ]);
    $paragraph->save();
    $this->messenger->addStatus($this->t('%label paragraph has been created.', [
      '%label' => $target_bundle['label'],
    ]));

    $this->removeOriginalTag($original_tag, $items, $delta);
    $stack = $items->getValue();
    $replacement = [$stack[$delta], $paragraph];
    if ('before' === $location) {
      $replacement = array_reverse($replacement);
    }
    array_splice($stack, $delta, 1, $replacement);
    $items->setValue($stack);
  }

  protected function removeOriginalTag(string $original_tag, $items, $delta) {
    if (!$items->get($delta)) {
      return;
    }
    $foo = $this->fieldTagService->normalizeItemFieldTag($items->get($delta));
    $foo = strval(FieldTag::create(['tag' => $foo])->removeTag($original_tag));
    $items->get($delta)->set('field_tag', $foo);
  }

}

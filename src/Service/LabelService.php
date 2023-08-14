<?php

namespace Drupal\field_tag\Service;

use Drupal\Core\Entity\EntityTypeRepositoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\field_tag\Plugin\Validation\Constraint\FieldTagConstraint;

class LabelService {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  public function __construct(\Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  public function scope(?object $scope_object): array {
    $labels = [
      FieldTagConstraint::SCOPE_ENTITY => 'Entity',
      FieldTagConstraint::SCOPE_BUNDLE => 'Bundle',
      FieldTagConstraint::SCOPE_FIELD => 'Field',
      FieldTagConstraint::SCOPE_FIELD_ITEM => 'Field item',
    ];

    if (empty($scope_object)) {
      return $labels;
    }

    list($entity, $field_item_list, $field_item) = (new \Drupal\field_tag\Helpers\ExplodeScopeObject())($scope_object);

    if ($entity) {
      $labels[FieldTagConstraint::SCOPE_ENTITY] = (string) $entity->getEntityType()
        ->getLabel();
      $bundle_type_id = $entity->getEntityType()->getBundleEntityType();
      try {
        $bundle_label = $this->entityTypeManager
          ->getStorage($bundle_type_id)
          ->load($entity->bundle())
          ->label();
      }
      catch (\Exception $e) {
        $bundle_label = $entity->getEntityType()->getLabel();
      }
      $labels[FieldTagConstraint::SCOPE_BUNDLE] = (string) $bundle_label;
    }

    if ($field_item_list) {
      $labels[FieldTagConstraint::SCOPE_FIELD] = $field_item_list
        ->getFieldDefinition()
        ->getLabel();
      $labels[FieldTagConstraint::SCOPE_FIELD_ITEM] = $labels[FieldTagConstraint::SCOPE_FIELD];
    }

    if ($field_item) {
      $labels[FieldTagConstraint::SCOPE_FIELD_ITEM] = sprintf('%s #%d', $labels[FieldTagConstraint::SCOPE_FIELD], 1 + (int) $field_item->getName());
    }

    ksort($labels);

    return $labels;
  }
}

<?php

namespace Drupal\field_tag;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\field_tag\Entity\FieldTagInterface;

/**
 * Defines the storage handler class for Field tag entities.
 *
 * This extends the base storage class, adding required special handling for
 * Field tag entities.
 *
 * @ingroup field_tag
 */
class FieldTagStorage extends SqlContentEntityStorage implements FieldTagStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(FieldTagInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {field_tag_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {field_tag_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(FieldTagInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {field_tag_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('field_tag_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}

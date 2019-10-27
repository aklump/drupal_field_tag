<?php

namespace Drupal\field_tag;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface FieldTagStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Field tag revision IDs for a specific Field tag.
   *
   * @param \Drupal\field_tag\Entity\FieldTagInterface $entity
   *   The Field tag entity.
   *
   * @return int[]
   *   Field tag revision IDs (in ascending order).
   */
  public function revisionIds(FieldTagInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Field tag author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Field tag revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\field_tag\Entity\FieldTagInterface $entity
   *   The Field tag entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(FieldTagInterface $entity);

  /**
   * Unsets the language for all Field tag with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}

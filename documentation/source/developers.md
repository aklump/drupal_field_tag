# Developers

* The value of `fieldTag` on a `\Drupal\Core\Field\FieldItemInterface` is read only.  It gets added to the item when one calls `\Drupal\field_tag\FieldTagService::attachTags`.  It is completely ignored during entity save operations, except that it may be unset if `field_tag` is also present on the field item.

* The value of `field_tag` on a `\Drupal\Core\Field\FieldItemInterface` is for entity save operations.  If present, this value will overwrite the existing value of the tag for that item.  This is a string and represents the full tag value, which may be CSV of multiple tags, e.g., 'foo, bar'.  During entity save when `field_tag` is present, `fieldTag` will be unset.

* If `$entity->field_tag_sync`, an array, contains a field name of a tag-enabled field.  During entity save operations, all existing tags for that field will be removed, and only those that are present as `field_tag` will be saved.

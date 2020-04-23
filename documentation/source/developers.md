# Developers

* The value of `fieldTag` on a `\Drupal\Core\Field\FieldItemInterface` is read only.  It gets added to the item when one calls `\Drupal\field_tag\FieldTagService::attachTags`.  It is completely ignored during entity save operations, except that it may be unset if `field_tag` is also present on the field item.

* The value of `field_tag` on a `\Drupal\Core\Field\FieldItemInterface` is for entity save operations.  If present, this value will overwrite the existing value of the tag for that item.  This is a string and represents the full tag value, which may be CSV of multiple tags, e.g., 'foo, bar'.  During entity save when `field_tag` is present, `fieldTag` will be unset.

* If `$entity->field_tag_sync`, an array, contains a field name of a tag-enabled field, then during entity save operations, all existing field tags for all items in that field will be deleted.  Then each items will be iterated over and only those which have `field_tag` will be saved.  **You must understand how this works**, before you go programmatically handling field_tag CRUD operations.  For example.

        $item = $node->field_images->get(0)->getValue();
        $item['field_tag'] = 'new tag';
        $node->field_images->filter(function () {
          return FALSE;
        })->appendItem($item);
        
        // This line is VERY important because of the use of the filter method
        // above.  If you did not include this line, then you would potentially
        // have orphaned field tags.
        $node->field_tag_sync[] = 'field_images';
        
        $node->save();

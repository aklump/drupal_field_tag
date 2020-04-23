# Developers

* This provides the UI and storage mechanism for field content tagging.  It creates a new entity type _field_tag_.  It's up to you to implement use cases for the data.
* See _field_tag.api.php_ for code examples.
* **Never rely on the id of a field tag entity beyond the scope of a single request. The ids should be considered ephemeral.**
* The value of `fieldTag` on a `\Drupal\Core\Field\FieldItemInterface` is read only.  It gets added to the item when one calls `\Drupal\field_tag\FieldTagService::attachTags`.  It is completely ignored during entity save operations, and will be unset at that time.

* The value of `field_tag` on a `\Drupal\Core\Field\FieldItemInterface` is for entity save operations.  If present, this value will overwrite the existing value of the tag for that field item.  This is a string and represents the full tag value, which may be CSV of multiple tags, e.g., 'foo, bar'.

        $node->field_images->get(0)->field_tag = 'foo, bar, baz';
        $node->save();

* If `$entity->field_tag_sync`, an array, contains a field name of a tag-enabled field, then during entity save operations, all existing field tags for all items in that field will be deleted.  Then the field items will be iterated over and only those which have a `field_tag` value will have field tag entities created.  **You must understand how this works**, before you go programmatically handling field_tag CRUD operations.  For example.

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

* There is a PDF of manual tests which must suffice until other tests can be written.

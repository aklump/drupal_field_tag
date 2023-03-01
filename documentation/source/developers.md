# Developers

* This provides the UI and storage mechanism for field content tagging.  It creates a new entity type _field_tag_.  It's up to you to implement use cases for the data.
* See _field_tag.api.php_ for code examples.
* **Never rely on the id of a field tag entity beyond the scope of a single request. The ids should be considered ephemeral.**
* The value of `fieldTag` on a `\Drupal\Core\Field\FieldItemInterface` is read only.  It gets added to the item when one calls `\Drupal\field_tag\FieldTagService::attachTags`.  It is completely ignored during entity save operations, and will be unset at that time.
* The value of `field_tag` on a `\Drupal\Core\Field\FieldItemInterface` is for entity save operations.  If present, this value will overwrite the existing value of the tag for that field item.  This is a string and represents the full tag value, which may be CSV of multiple tags, e.g., 'foo, bar'.

        $node->field_images->get(0)->field_tag = 'foo, bar, baz';
        $node->save();

* There is a PDF of manual tests which must suffice until other tests can be written.
* Run unit tests with `./bin/run_unit_tests.sh`

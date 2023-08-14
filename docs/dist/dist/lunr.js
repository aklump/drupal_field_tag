var lunrIndex = [{"id":"readme","title":"Field Tags Module","body":"![Implementation](..\/..\/images\/implementation.jpg)\n\n## Summary\n\nProvides a means to attach a tag-style input box to any entity field, which allows content managers to tag the field content.  Developers may take advantage of this additional metadata when manipulating fields.\n\nThe use case for which this was written is this.  Allow the tagging of images on an multiple value image field to indicate which image is the `hero` image, which image is the `thumb` image, and untagged images are just that.  It allows the content managers to indicate the role the image is playing for that given entity.\n\n**Visit  for full documentation.**\n\n## About Tags\n\n* The field tag input box is a CSV string separating one or more tags, e.g. `foo` or `foo, bar`.\n* Tags are not case-sensitive.\n* Tags may contain spaces.\n* Tags must be unique; duplicates will be removed.\n\n## Configuration\n\n1. Enable this module.\n1. Visit the _Manage fields_ page for the entity you've picked.\n1. Click on the _Edit_ button for the given field.\n1. Enable the _Field Tag_, and adjust settings as necessary.  ![Settings](..\/..\/images\/settings.jpg)\n1. Give the permission _Use field tagging_ to the correct user roles.\n1. Visit an entity edit page and make sure you see the tag field as configured.\n\n### What Happens When a Field is Deleted\n\nThe field tags themselves exist as `FieldTag` entity instances.  When a field which is _field tag enabled_ on an entity type is deleted, all field tags that are associated with that entity type\/field are marked with a `1` in the `deleted` column in the `field_tag` table.  They still exist in the database but are not going to load via the normal field tag API, attach methods, etc.  You can still load them using `FieldTag::load()` if necessary, or access them via the database for reference.\n\n## Manage form display\n\n1. Node forms will include a list of field tags in the Advanced area, but only if one or more fields have field tags enabled.\n1. You may control this form element by going to _Manage form display_ for a given node type and changing the weight or disabling this element.\n\n## Contributing\n\nIf you find this project useful... please consider [making a donation](https:\/\/www.paypal.com\/cgi-bin\/webscr?cmd=_s-xclick&hosted_button_id=4E5KZHDQCEUV8&item_name=Gratitude%20for%20aklump%2Ffield_tag).\n\n## Contact The Developer\n\n* In the Loft Studios\n* Aaron Klump - Web Developer\n* sourcecode@intheloftstudios.com\n* 360.690.6432\n* PO Box 29294 Bellingham, WA 98228-1294\n*\n*"},{"id":"relationships","title":"Relationships","body":"The field tag entities are related to a given field on a parent entity. Specifically to a single delta on said field. In other words, the thing that is tagged by a `field_tag` entity can only be known when you have: entity + field + delta.\n\n![relationships](..\/..\/images\/relationships.png)\n\nThere is a possible further relationship **when the field is an entity reference field** such as a file field or a paragraph. In this case the field tag will be related to the target entity as well.\n\n![relationships](..\/..\/images\/relationships_references.png)"},{"id":"issues","title":"Known Issues","body":"## Cannot Tag Some Empty Items\n\nIf you try to add a tag to a textfield that is empty, and save the entity, the tag will not save.\n\n![title](..\/..\/images\/issue1.png)\n\nHowever, if you tag an item and then later remove the value making it empty, the tag remains."},{"id":"changelog","title":"Changelog","body":"All notable changes to this project will be documented in this file.\n\nThe format is based on [Keep a Changelog](https:\/\/keepachangelog.com\/en\/1.0.0\/), and this project adheres to [Semantic Versioning](https:\/\/semver.org\/spec\/v2.0.0.html).\n\n## Todo\n\n- When field_tag_paragraph processes an item, redirect to the node edit form to immediately begin editing.\n- Replace the need for attachTags with a hook like: hook_entity_storage_load() or hook_entity_field_values_init()\n\n## [0.4.0] - 2023-03-01\n\n### Changed\n\n- The `\\Drupal\\field_tag\\Event\\TagEvent` now delivers an instance of FieldTag that has only the added or removed tags. You will most likely **need to update your event handlers**. Here is an example of a diff.\n\n```\n- if ('some_tag' === $event->getTag()) {\n+ if ($event->getFieldTag()->hasTag('some_tag')) {\n```\n\n### Deprecated\n\n- `\\Drupal\\field_tag\\FieldTagService::normalizeFieldTagValue`. Use `implode(', ', \\Drupal\\field_tag\\Tags::create($value)->all());`\n- `\\Drupal\\field_tag\\FieldTagService::doesEntityUseFieldTags`. Use `\\Drupal\\field_tag\\FieldTagService::getTaggedFieldDefinitionsByEntity()` cast to boolean\n- `\\Drupal\\field_tag\\FieldTagService::getFieldTagsAsArray`. Use `Tags::create($value)->all()` instead.\n- `\\Drupal\\field_tag\\Event\\TagEvent::getEntity` use `\\Drupal\\field_tag\\Event\\TagEvent::getFieldTag->getParentEntity()` instead.\n- `\\Drupal\\field_tag\\Event\\TagEvent::getTag` use `\\Drupal\\field_tag\\Event\\TagEvent::getFieldTag()->getValue()` instead.\n\n### Removed\n\n- lorem\n\n### Fixed\n\n- lorem\n\n### Security\n\n- lorem\n\n## [0.3.0] - 2022-02-09\n\n### Added\n\n- Dispatch event `\\Drupal\\field_tag\\Event\\FieldTagEvents::TAG_ADDED` when a tag is added to a parent.\n- Dispatch event `\\Drupal\\field_tag\\Event\\FieldTagEvents::TAG_REMOVED` when a tag is removed from a parent.\n\n## [0.2.0] - 2022-01-18\n\n### Added\n\n- Field tag paragraphs module\n- `FieldTag::removeTag()` method.\n\n## [0.0.19] - 2020-04-22\n\n### Fixed\n\n- More bugs that might loose the field tags during certain operations.\n\n### Added\n\n- Manual tests for QA.\n\n## [0.0.17] - 2020-04-16\n\n### Fixed\n\n- A bug that might loose the field tags for an field item, on multiple item fields, if items with lower deltas were removed via AJAX.\n\n## [8.x-0.0.13] - 2020-03-16\n\n### Changed\n\n- `loadFromParent` has been replaced by `loadByParentField`.\n- `\\Drupal\\field_tag\\FieldTagService::attachTags` now sets the FieldTag instance as `$item->fieldTag` instead of `$item->field_tag`. The latter should only be used when you are setting the value, as this is what will be saved when the entity is saved."},{"id":"developers","title":"Developers","body":"* This provides the UI and storage mechanism for field content tagging.  It creates a new entity type _field\\_tag_.  It's up to you to implement use cases for the data.\n* See _field\\_tag.api.php_ for code examples.\n* **Never rely on the id of a field tag entity beyond the scope of a single request. The ids should be considered ephemeral.**\n* The value of `fieldTag` on a `\\Drupal\\Core\\Field\\FieldItemInterface` is read only.  It gets added to the item when one calls `\\Drupal\\field_tag\\FieldTagService::attachTags`.  It is completely ignored during entity save operations, and will be unset at that time.\n* The value of `field_tag` on a `\\Drupal\\Core\\Field\\FieldItemInterface` is for entity save operations.  If present, this value will overwrite the existing value of the tag for that field item.  This is a string and represents the full tag value, which may be CSV of multiple tags, e.g., 'foo, bar'.\n\n        $node->field_images->get(0)->field_tag = 'foo, bar, baz';\n        $node->save();"},{"id":"testing","title":"Testing","body":"## Unit Tests\n\n1. From the module root.\n2. `.\/bin\/run_unit_tests.sh`\n\n## Integration Tests\n\n1. From Drupal app root.\n2. `.\/bin\/run_integration_tests.sh --filter=field_tag`\n\n## Manual Tests\n\n1. See files in _tests\/src\/Manual_"},{"id":"events","title":"Tag-Related Events","body":"If you need to react to a tag being added or removed you can subscribe to the event(s): `\\Drupal\\field_tag\\Event\\FieldTagEvents::TAG_REMOVED` and\/or `\\Drupal\\field_tag\\Event\\FieldTagEvents::TAG_ADDED`.\n\nSee `\\Drupal\\field_tag\\Event\\TagEvent`, for the context available.\n\nHere is an example implementation:\n\n```php\nclass Foo implements \\Symfony\\Component\\EventDispatcher\\EventSubscriberInterface {\n\n  public static function getSubscribedEvents() {\n    return [\n      \\Drupal\\field_tag\\Event\\FieldTagEvents::TAG_ADDED => 'invalidateBlockCache',\n      \\Drupal\\field_tag\\Event\\FieldTagEvents::TAG_REMOVED => 'invalidateBlockCache',\n    ];\n  }\n\n  public function invalidateBlockCache(TagEvent $event) {\n\n    \/\/ If we see the \"highlights\" tag adding\/removing, we need to rebuild the\n    \/\/ highlights block (id 17), so we have to invalidate the cache tag.\n    if ($event->getFieldTag()->has('highlights') {\n      $cid = 'block_content:17';\n      $this->cacheTagsInvalidator->invalidateTags([$cid]);\n    }\n  }\n\n}\n```\n\n## Deleting Parent Entities\n\nWhen parent entities (those with field tagging enabled) are deleted, the associated field tag entities are also deleted. This also fires the `\\Drupal\\field_tag\\Event\\FieldTagEvents::TAG_REMOVED` event."},{"id":"in_code","title":"Working Programatically with Field Tags.","body":"[See Code Examples](@code_examples)"},{"id":"drupal_migrations","title":"Drupal Migrations","body":"Here's an example of how you might tag an image field during a migration, this assumes `field_images` has field tagging enabled and that 1) your source has no field tags or 2) you wish to replace those existing field tags.\n\n```yaml\nprocess:\n  field_images:\n    plugin: sub_process\n    source: field_hero_images\n    process:\n      target_id: fid\n      alt: alt\n      title: title\n      width: width\n      height: height\n      field_tag:\n        plugin: default_value\n        default_value: hero\n```\n\nAnd here is a migration where source has field tags and you wish to merge in new ones.\n\n```yaml\nprocess:\n    # First copy over as is from source.\n    field_images: field_images\n\n    # Then add two tags to the first element only.\n    field_images\/0\/field_tag:\n      - plugin: field_tag_add\n        source: field_images\/0\/field_tag\n        field_tag: cover, card\n```"},{"id":"code_examples","title":"Code Examples","body":"```php"},{"id":"validation","title":"Tag Validation Rules","body":"It's possible to setup rules for how tags are used including, minimums, maximums, required, or invalid. Without defining these validation rules anything can be entered as a tag without constraint.\n\nTo define rules you will implement `hook_field_tag_validation_rules`; see _field\\_tag.api.php_ for more info and examples. Every rule change requires that you rebuild the Drupal caches.\n\n```php\nfunction hook_field_tag_validation_rules() {\n  return [\n    [\n      'tag_value' => 'thumb',\n      'only_on_entities' => ['node'],\n      'only_on_bundles' => ['page', 'blog_entry'],\n      'only_on_fields' => ['field_photos'],\n      'field_max' => 1,\n    ],\n    [\n      'tag_regex' => '^(english|spanish|french)$',\n      'only_on_entities' => ['node'],\n      'only_on_fields' => ['field_articles'],\n    ],\n  ];\n}\n```\n\nThe rules are implemented as [entity and field constraints](https:\/\/www.drupal.org\/docs\/drupal-apis\/entity-api\/entity-validation-api\/entity-validation-api-overview). @see `\\Drupal\\field_tag\\Plugin\\Validation\\Constraint\\FieldTagConstraint`."},{"id":"retroactive","title":"Retroactive Validation","body":"You can use the following snippets to help in writing rules retroactively.\n\nDetermine the entity types currently having tags:\n\n```sql\nselect distinct(parent_entity) as 'only_on_entities'\nfrom field_tag;\n```\n\nThen for each entity type do something like the flowing examples for nodes and paragraphs.\n\n```sql\nselect distinct tag           as tag_value,\n                parent_entity as only_on_entities,\n                n.type        as only_on_bundles,\n                field_name    as only_on_fields\nfrom field_tag ft\n         join node n on n.nid = ft.parent_id\nwhere deleted < 1\n  and parent_entity = 'node'\norder by tag_value;\n```\n\n```sql\nselect distinct tag           as tag_value,\n                parent_entity as only_on_entities,\n                p.type        as only_on_bundles,\n                field_name    as only_on_fields\nfrom field_tag ft\n         join paragraphs_item p on p.id = ft.parent_id\nwhere deleted < 1\n  and parent_entity = 'paragraph'\norder by tag_value;\n```\n\nThen you can analyze single tags with something like this; notice the `tag like...` addition.\n\n```sql\nselect distinct tag           as tag_value,\n                parent_entity as only_on_entities,\n                n.type        as only_on_bundles,\n                field_name    as only_on_fields\nfrom field_tag ft\n         join node n on n.nid = ft.parent_id\nwhere deleted < 1\n  and parent_entity = 'node'\n  and tag like \"%vimeo%\";\n```"}]
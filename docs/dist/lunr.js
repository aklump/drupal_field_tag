var lunrIndex = [{"id":"block_field_item_access","title":"Block Field Item Access by Tags","body":"This examples uses tags `survey` and `no survey` to hide and show field items on a block based on the presence of an active survey.\n\n```php\n\/**\n * Implements hook_block_view_alter().\n *\/\nfunction my_module_block_view_alter(array &$build, \\Drupal\\Core\\Block\\BlockPluginInterface $block) {\n  \/\/ Only target this one block where we expect these tags to be used.\n  if ($block->getPluginId() === 'block_content:fc56a3f4-9750-4650-9274-5bf8088a6fb9') {\n    $build['#pre_render'][] = [\n      FieldTagPreRender::class,\n      'processBlockContent',\n    ];\n  }\n}\n```\n\n```php"},{"id":"changelog","title":"Changelog","body":"All notable changes to this project will be documented in this file.\n\nThe format is based on [Keep a Changelog](https:\/\/keepachangelog.com\/en\/1.0.0\/), and this project adheres to [Semantic Versioning](https:\/\/semver.org\/spec\/v2.0.0.html).\n\n## Todo\n\n- When field_tag_paragraph processes an item, redirect to the node edit form to immediately begin editing.\n- Replace the need for attachTags with a hook like: hook_entity_storage_load() or hook_entity_field_values_init()\n\n## [0.7.5] - 2024-11-26\n\n### Fixed\n\n- Fix bug with event dispatch argument order.\n\n## [0.7.0] - 2024-06-13\n\n### Added\n\n- Support for Drupal 10\n\n## [0.6.3] - 2024-03-27\n\n### Fixed\n\n- Tagging of block_content has been fixed. It broke somewhere along the way and there is no test coverage yet. Still no test coverage.\n\n## [0.6.0] - 2024-03-13\n\n### Added\n\n- The `Drupal\\field_tag\\Rule\\Rule::CALLABLE` condition. See docs for usage info.\n\n## [0.5.0] - 2023-08-13\n\n### Added\n\n- `hook_field_tag_validation_rules()`  You can now add constraints to your field_tag use cases. See _field_tag.api.php_ for more info.\n\n## [0.4.0] - 2023-03-01\n\n### Changed\n\n- The `\\Drupal\\field_tag\\Event\\TagEvent` now delivers an instance of FieldTag that has only the added or removed tags. You will most likely **need to update your event handlers**. Here is an example of a diff.\n\n```\n- if ('some_tag' === $event->getTag()) {\n+ if ($event->getFieldTag()->hasTag('some_tag')) {\n```\n\n### Deprecated\n\n- `\\Drupal\\field_tag\\FieldTagService::normalizeFieldTagValue`. Use `implode(', ', \\Drupal\\field_tag\\Tags::create($value)->all());`\n- `\\Drupal\\field_tag\\FieldTagService::doesEntityUseFieldTags`. Use `\\Drupal\\field_tag\\FieldTagService::getTaggedFieldDefinitionsByEntity()` cast to boolean\n- `\\Drupal\\field_tag\\FieldTagService::getFieldTagsAsArray`. Use `Tags::create($value)->all()` instead.\n- `\\Drupal\\field_tag\\Event\\TagEvent::getEntity` use `\\Drupal\\field_tag\\Event\\TagEvent::getFieldTag->getParentEntity()` instead.\n- `\\Drupal\\field_tag\\Event\\TagEvent::getTag` use `\\Drupal\\field_tag\\Event\\TagEvent::getFieldTag()->getValue()` instead.\n\n### Removed\n\n- lorem\n\n### Fixed\n\n- lorem\n\n### Security\n\n- lorem\n\n## [0.3.0] - 2022-02-09\n\n### Added\n\n- Dispatch event `\\Drupal\\field_tag\\Event\\FieldTagEvents::TAG_ADDED` when a tag is added to a parent.\n- Dispatch event `\\Drupal\\field_tag\\Event\\FieldTagEvents::TAG_REMOVED` when a tag is removed from a parent.\n\n## [0.2.0] - 2022-01-18\n\n### Added\n\n- Field tag paragraphs module\n- `FieldTag::removeTag()` method.\n\n## [0.0.19] - 2020-04-22\n\n### Fixed\n\n- More bugs that might loose the field tags during certain operations.\n\n### Added\n\n- Manual tests for QA.\n\n## [0.0.17] - 2020-04-16\n\n### Fixed\n\n- A bug that might loose the field tags for an field item, on multiple item fields, if items with lower deltas were removed via AJAX.\n\n## [8.x-0.0.13] - 2020-03-16\n\n### Changed\n\n- `loadFromParent` has been replaced by `loadByParentField`.\n- `\\Drupal\\field_tag\\FieldTagService::attachTags` now sets the FieldTag instance as `$item->fieldTag` instead of `$item->field_tag`. The latter should only be used when you are setting the value, as this is what will be saved when the entity is saved."},{"id":"code_examples","title":"Code Examples","body":"```php\n\/**\n * @file\n * Defines the API functions provided by the field_tag module.\n *\/\n\n\/\/ There are two classes to consider when working with field tags.  The\n\/\/ low-level class \\Drupal\\field_tag\\Tags is purely utilitarian and has no\n\/\/ dependencies and may be more intuitive in some cases; it has not concept of\n\/\/ parent entity.  The other class, which represents the FieldTag entity is\n\/\/ \\Drupal\\field_tag\\Entity\\FieldTag and should also be taken into account.\n\n```\n\n```php\n\/**\n * Create a field tag object attached to a parent entity.\n *\/\n$instance = \\Drupal\\field_tag\\Entity\\FieldTag::createFromTags(\n  \\Drupal\\field_tag\\Tags::create('foo,bar'),\n  $parent_entity,\n  'field_foo',\n);\n\n```\n\n```php\n\/**\n * Create a field tag object by array\n *\/\n$array_of_tags = ['foo', 'bar'];\n$instance = \\Drupal\\field_tag\\Entity\\FieldTag::createFromTags(\n  \\Drupal\\field_tag\\Tags::create(...$array_of_tags),\n  $parent_entity,\n  'field_foo',\n);\nTRUE === $instance->hasTag('foo');\n\n```\n\n```php\n\/**\n * Programmatically tagging fields.\n *\n * You must use ->field_tag if you want the entity save operation to persist the\n * values.  You cannot use ->fieldTag for adding tags to be saved in the\n * database.\n *\/\n\n$entity->get('field_images')->get(0)->field_tag = 'foo, bar, baz';\n$entity->save();\n\n```\n\n```php\n\/**\n * Programmatically manipulating field tags.\n *\n * There are two approaches for working with tags already attached to an\n * entity. Each one has it's merits so review both and then decide which is\n * most\n * appropriate for your use case.\n *\n * The first approach shows loading a given tag entity directly.  This method\n * is nice because if the field is not already tagged--that is the field_tag\n * entity does not yet exist--you still receive a configured instance of a\n * field_tag entity in return (not yet saved, however).\n *\n * In both methods, once the field_tag entity is provided, you work with it the\n * same, using on it's own methods.\n *\/\n$field_tag_entity = \\Drupal\\field_tag\\Entity\\FieldTag::loadByParentField($entity, 'field_images', 0);\n\n\/\/ Check if a tag exists based on the CSV split.\n$has_tag = $field_tag_entity->hasTag('hero');\n\n\/\/ Get the string value of the field_tag.  Both yield the same result.\n$value = $field_tag_entity->getValue();\n$value = (string) $field_tag_entity;\n\n\/\/ Get an array of tags split by CSV.\n$tags = $field_tag_entity->all();\n\n```\n\n```php\n\/**\n * Second approach, first attach all tags to parent, then manipulate.\n *\n * The alternative approach is to attach all field_tags to an entity and then\n * work with them in that context.  An example follows.\n *\/\n\\Drupal::service('field_tag')->attachTags($entity);\nforeach ($entity->get('field_images') as $item) {\n  if ($field_tag_entity = $item->fieldTag) {\n    $has_tag = $field_tag_entity->hasTag();\n    $tags = $field_tag_entity->all();\n    $value = $field_tag_entity->getValue();\n  }\n}\n\n```\n\n```php\n\/**\n * Pluck out a single image tagged by 'hero'.\n *\/\n$hero_image_references = \\Drupal::service('field_tag')\n  ->attachTags($node)\n  ->getItemsTaggedBy('hero', 'field_images');\nif ($hero_image_references && ($image_file = $hero_images[0]->get('entity')\n    ->getTarget()\n    ->getEntity())) {\n  $uri = $image_file->getFileUri();\n}\n\n```\n\n```php\n\/**\n * Another version, more verbose.\n *\n * Get the image entity and URI of the first image tagged with 'hero'.  It is\n * assumed that $entity has an image reference field: field_images, and field\n * tagging has been configured for that field, and at least one field has been\n * tagged with 'hero'.\n *\/\n$hero_image_entity = NULL;\n$hero_image_uri = NULL;\nforeach ($entity->get('field_images') as $image_item) {\n  if ($image_item->field_tag && $image_item->field_tag->hasTag('hero')) {\n    $hero_image_entity = $image_item->get('entity')->getTarget()->getValue();\n    $hero_image_uri = $hero_image_entity->get('uri')->value;\n    break;\n  }\n}\n\n```\n\n```php\n\/**\n * A complex Paragraphs example.\n *\n * In this example, a node has a paragraph reference as field_paragraphs, which\n * allows multiple items.  Tagging is set up on field_paragraphs.  (That is, on\n * the node, not the paragraph entity.)  When given the paragraph, we check to\n * see if it is tagged on the parent by a given tag and then do something to it.\n *\/\n$parent = $paragraph->getParentEntity();\n$parent_field = $paragraph->get('parent_field_name')->get(0)->value;\n$items = \\Drupal::service('field_tag')\n  ->attachTags($parent)\n  ->getItemsTaggedBy('golden', $parent_field);\nforeach ($items as $item) {\n  if ($item->target_id == $paragraph->id()) {\n    \/\/ React to the fact that this paragraph is tagged by 'golden'.\n  }\n}\n\n```\n\n```php\n\/**\n * Getting tags for a referenced paragraph.\n *\n * In this example we want to find the first tag that begins with '#' to use as\n * an id.  We're assuming that a tag such as \"#foo\" has been entered on the\n * parent's reference field.\n *\/\n\/\/ First get an array of \\Drupal\\field_tag\\Entity\\FieldTag entities.\n$field_tag_entities = $this->service('field_tag')\n  ->getFieldTagsByParagraph($paragraph);\n\n\/\/ Then merge them into a single array of single tag strings.\n$tags = array_flatten(array_map(function ($field_tag) {\n  return $field_tag->all();\n}, $field_tag_entities));\n\n\/\/ Now we want to get the first tag that begins with '#', so we can use that to\n\/\/ render an id attribute.\n$id = array_values(array_filter($tags, function ($tag) {\n  return strpos($tag, '#') === 0;\n}))[0];\n\n```\n\n```php\n\/**\n * Implements hook_field_tag_tags_info().\n *\n * Provides information about the available field tags and facilitates grouping.\n *\n * @return array\n *   An array with these keys:\n *   - types\n *   - tags\n *   Types should be keyed by the machine name that is used to key the tags\n *   array.  Each value in types should contain:\n *   - name string|\\Drupal\\Core\\StringTranslation\\TranslatableMarkup The name\n *   of the group.\n *   - description string|\\Drupal\\Core\\StringTranslation\\TranslatableMarkup The\n *   description of the group. Tags should be keyed by the type and each type\n *   array is an array of tags with the following keys:\n *   - tag string|\\Drupal\\Core\\StringTranslation\\TranslatableMarkup The\n *   lowercase tag value.\n *   - description string|\\Drupal\\Core\\StringTranslation\\TranslatableMarkup A\n *   description of the tag, how it is used, examples, links, etc.\n *\/\nfunction hook_field_tag_tags_info() {\n  return [\n    'types' => [\n      'visibility' => [\n        'name' => t('Visibility'),\n        'description' => t('Affect the visibility and\/or device visibility.'),\n      ],\n      'image' => [\n        'name' => t('Image'),\n        'description' => t('Field tags that apply to images.'),\n      ],\n    ],\n    'tags' => [\n      'layout' => [\n        [\n          'tag' => '1 of foo',\n          'description' => t('Tags that follow this pattern define a group that is sequenced every page load; only one item is loaded per HTTP request.  You can see this at work on Sanctuaries of Silence (\/node\/9146), it\\'s how we get a different VR experience to show up on each load.  The group `{id}` can be any arbitrary value, e.g. \"vr\", \"foo\", \"bar\".'),\n        ],\n        [\n          'tag' => '#foo',\n          'description' => t('Where  is an HTML ID, e.g., .  See _Creating HTML Anchors Using Tags_.'),\n        ],\n      ],\n      'language' => [\n        [\n          'tag' => 'english',\n          'description' => t('Indicates the tagged item is in the English language.  Used for downloads and resources.'),\n        ],\n        [\n          'tag' => 'spanish',\n          'description' => t('Indicates the tagged item is in the Spanish language.  Used for downloads and resources.'),\n        ],\n      ],\n    ],\n  ];\n}\n\n```\n\n```php\n\/**\n * Allow extensions to provide tag usage rules for entities and fields.\n *\n * @return \\Drupal\\field_tag\\Rule\\Rule[]\n *\/\nfunction hook_field_tag_validation_rules(): array {\n  return [\n    (new \\Drupal\\field_tag\\Rule\\Rule())\n      ->condition(\\Drupal\\field_tag\\Rule\\Rule::TAG_VALUE, 'thumb')\n      ->condition(\\Drupal\\field_tag\\Rule\\Rule::ENTITY, 'node')\n      ->condition(\\Drupal\\field_tag\\Rule\\Rule::BUNDLE, [\n        'page',\n        'blog_entry',\n      ], 'in')\n      ->condition(\\Drupal\\field_tag\\Rule\\Rule::HAS_FIELD, 'field_photos')\n      ->require(\\Drupal\\field_tag\\Rule\\Rule::TAG_MIN_PER_FIELD, 1),\n\n    (new \\Drupal\\field_tag\\Rule\\Rule())\n      ->condition(\\Drupal\\field_tag\\Rule\\Rule::TAG_REGEX, '\/^(english|spanish|french)$\/')\n      ->require(\\Drupal\\field_tag\\Rule\\Rule::ENTITY, 'node')\n      ->require(\\Drupal\\field_tag\\Rule\\Rule::TAGGED_FIELD, 'field_articles'),\n  ];\n}\n```"},{"id":"developers","title":"Developers","body":"* This provides the UI and storage mechanism for field content tagging.  It creates a new entity type _field\\_tag_.  It's up to you to implement use cases for the data.\n* See _field\\_tag.api.php_ for code examples.\n* **Never rely on the id of a field tag entity beyond the scope of a single request. The ids should be considered ephemeral.**\n* The value of `fieldTag` on a `\\Drupal\\Core\\Field\\FieldItemInterface` is read only.  It gets added to the item when one calls `\\Drupal\\field_tag\\FieldTagService::attachTags`.  It is completely ignored during entity save operations, and will be unset at that time.\n* The value of `field_tag` on a `\\Drupal\\Core\\Field\\FieldItemInterface` is for entity save operations.  If present, this value will overwrite the existing value of the tag for that field item.  This is a string and represents the full tag value, which may be CSV of multiple tags, e.g., 'foo, bar'.\n\n        $node->field_images->get(0)->field_tag = 'foo, bar, baz';\n        $node->save();"},{"id":"drupal_migrations","title":"Drupal Migrations","body":"Here's an example of how you might tag an image field during a migration, this assumes `field_images` has field tagging enabled and that 1) your source has no field tags or 2) you wish to replace those existing field tags.\n\n```yaml\nprocess:\n  field_images:\n    plugin: sub_process\n    source: field_hero_images\n    process:\n      target_id: fid\n      alt: alt\n      title: title\n      width: width\n      height: height\n      field_tag:\n        plugin: default_value\n        default_value: hero\n```\n\nAnd here is a migration where source has field tags and you wish to merge in new ones.\n\n```yaml\nprocess:\n    # First copy over as is from source.\n    field_images: field_images\n\n    # Then add two tags to the first element only.\n    field_images\/0\/field_tag:\n      - plugin: field_tag_add\n        source: field_images\/0\/field_tag\n        field_tag: cover, card\n```"},{"id":"readme","title":"Field Tags Module","body":"![Implementation](..\/..\/images\/implementation.jpg)\n\n## Summary\n\nProvides a means to attach a tag-style input box to any entity field, which allows content managers to tag the field content. Developers may take advantage of this additional metadata when manipulating fields.\n\nThe use case for which this was written is this. Allow the tagging of images on an multiple value image field to indicate which image is the `hero` image, which image is the `thumb` image, and untagged images are just that. It allows the content managers to indicate the role the image is playing for that given entity.\n\n**Visit  for full documentation.**\n\n## About Tags\n\n* The field tag input box is a CSV string separating one or more tags, e.g. `foo` or `foo, bar`.\n* Tags are not case-sensitive.\n* Tags may contain spaces.\n* Tags must be unique; duplicates will be removed.\n\n## Install with Composer\n\n1. Because this is an unpublished package, you must define it's repository in\n   your project's _composer.json_ file. Add the following to _composer.json_ in\n   the `repositories` array:\n\n    ```json\n    {\n     \"type\": \"github\",\n     \"url\": \"https:\/\/github.com\/aklump\/drupal_field_tag\"\n    }\n    ```\n1. Require this package:\n\n    ```\n    composer require drupal\/field_tag:^0.7\n    ```\n1. Add the installed directory to _.gitignore_\n\n   ```php\n   \/web\/modules\/custom\/field_tag\/\n   ```\n\n## Configuration\n\n1. Enable this module.\n1. Visit the _Manage fields_ page for the entity you've picked.\n1. Click on the _Edit_ button for the given field.\n1. Enable the _Field Tag_, and adjust settings as necessary.  ![Settings](..\/..\/images\/settings.jpg)\n1. Give the permission _Use field tagging_ to the correct user roles.\n1. Visit an entity edit page and make sure you see the tag field as configured.\n\n### What Happens When a Field is Deleted\n\nThe field tags themselves exist as `FieldTag` entity instances. When a field which is _field tag enabled_ on an entity type is deleted, all field tags that are associated with that entity type\/field are marked with a `1` in the `deleted` column in the `field_tag` table. They still exist in the database but are not going to load via the normal field tag API, attach methods, etc. You can still load them using `FieldTag::load()` if necessary, or access them via the database for reference.\n\n## Manage form display\n\n1. Node forms will include a list of field tags in the Advanced area, but only if one or more fields have field tags enabled.\n1. You may control this form element by going to _Manage form display_ for a given node type and changing the weight or disabling this element.\n\n## Contributing\n\nIf you find this project useful... please consider [making a donation](https:\/\/www.paypal.com\/cgi-bin\/webscr?cmd=_s-xclick&hosted_button_id=4E5KZHDQCEUV8&item_name=Gratitude%20for%20aklump%2Ffield_tag).\n\n## Contact The Developer\n\n* In the Loft Studios\n* Aaron Klump - Web Developer\n* sourcecode@intheloftstudios.com\n* 360.690.6432\n* PO Box 29294 Bellingham, WA 98228-1294\n*\n*"},{"id":"issues","title":"Known Issues","body":"## Cannot Tag Some Empty Items\n\nIf you try to add a tag to a textfield that is empty, and save the entity, the tag will not save.\n\n![title](..\/..\/images\/issue1.png)\n\nHowever, if you tag an item and then later remove the value making it empty, the tag remains."},{"id":"relationships","title":"Relationships","body":"The field tag entities are related to a given field on a parent entity. Specifically to a single delta on said field. In other words, the thing that is tagged by a `field_tag` entity can only be known when you have: entity + field + delta.\n\n![relationships](..\/..\/images\/relationships.png)\n\nThere is a possible further relationship **when the field is an entity reference field** such as a file field or a paragraph. In this case the field tag will be related to the target entity as well.\n\n![relationships](..\/..\/images\/relationships_references.png)"},{"id":"required_tags","title":"Required Tags","body":"## Exactly One Tag on a Specific Bundle Field\n\nHere is the scenario:  You have a node bundle called `blog_entry`, it has `field_images`, which allows multiple images. You need to ensure that exactly one of those images is designated as the thumbnail. You've decided to use the `thumb` tag for this designation. Later your theme can cherry pick that image and render it appropriately\n\nHere's how to write that rule:\n\n```php\nuse Drupal\\field_tag\\Rule;\n\n$rule = (new Rule())\n  ->condition(Rule::BUNDLE, 'blog_entry')\n  ->condition(Rule::HAS_FIELD, 'field_images')\n  ->require(Rule::TAG_VALUE, 'thumb')\n  ->require(Rule::TAG_MIN_PER_FIELD, 1)\n  ->require(Rule::TAG_MAX_PER_FIELD, 1);\n```\n\n## At Least One Tag on Any Entity\n\nHere is the scenario:  You have two fields attached to two entity types. For the node entity type it's used across multiple bundles, and you want this rule to cover all future bundles that have either of those fields; _this is pointed out to explain why the rule does not include `Rule::BUNDLE`_. However, you only want it to apply to the two current entity types, which is why you see `Rule::ENTITY`.\n\nThe reason for this rule, is that you've decided to display only some of those files in the teaser and you've designated the `teaser` tag to indicate this. You want to ensure that every `node,user` entity has at least one file between those two fields tagged `teaser`. That way you can display at least one item per node.\n\nHere's how to write that rule:\n\n```php\nuse Drupal\\field_tag\\Rule;\n\n$rule = (new Rule())\n  ->condition(Rule::ENTITY, ['node', 'user'], 'in')\n  ->condition(Rule::HAS_FIELD, ['field_images', 'field_pdfs'], 'in')\n  ->require(Rule::TAG_VALUE, 'teaser')\n  ->require(Rule::TAGGED_FIELD, ['field_images', 'field_pdfs'], 'in')\n  ->require(Rule::TAG_MIN_PER_ENTITY, 1);\n```\n\nHere's how to read it in plain English:\n\n> Any node or user entity that has `field_images` and\/or `field_pdfs` must have either of those fields tagged at least once with `teaser`."},{"id":"retroactive","title":"Retroactive Validation","body":"You can use the following snippets to help in writing rules retroactively.\n\nDetermine the entity types currently having tags:\n\n```sql\nselect distinct(parent_entity) as 'only_on_entities'\nfrom field_tag;\n```\n\nThen for each entity type do something like the flowing examples for nodes and paragraphs.\n\n```sql\nselect distinct tag           as tag_value,\n                parent_entity as only_on_entities,\n                n.type        as only_on_bundles,\n                field_name    as only_on_fields\nfrom field_tag ft\n         join node n on n.nid = ft.parent_id\nwhere deleted < 1\n  and parent_entity = 'node'\norder by tag_value;\n```\n\n```sql\nselect distinct tag           as tag_value,\n                parent_entity as only_on_entities,\n                p.type        as only_on_bundles,\n                field_name    as only_on_fields\nfrom field_tag ft\n         join paragraphs_item p on p.id = ft.parent_id\nwhere deleted < 1\n  and parent_entity = 'paragraph'\norder by tag_value;\n```\n\nThen you can analyze single tags with something like this; notice the `tag like...` addition.\n\n```sql\nselect distinct tag           as tag_value,\n                parent_entity as only_on_entities,\n                n.type        as only_on_bundles,\n                field_name    as only_on_fields\nfrom field_tag ft\n         join node n on n.nid = ft.parent_id\nwhere deleted < 1\n  and parent_entity = 'node'\n  and tag like \"%vimeo%\";\n```"},{"id":"validation","title":"Tag Validation Rules","body":"It's possible to setup rules for how tags are used including, minimums, maximums, required, or invalid. Without defining these validation rules anything can be entered as a tag without constraint.\n\nTo define rules you will implement `hook_field_tag_validation_rules`; see _field\\_tag.api.php_ for more info and examples. Every rule change requires that you rebuild the Drupal caches.\n\nThe rules are implemented as [entity and field constraints](https:\/\/www.drupal.org\/docs\/drupal-apis\/entity-api\/entity-validation-api\/entity-validation-api-overview). @see `\\Drupal\\field_tag\\Plugin\\Validation\\Constraint\\FieldTagConstraint`.\n\n## Explained\n\nEach rule consists of two parts: 1) the conditions to be met to apply the rule and 2) the requirements that must be met if the rule is applied.\n\n## When Are Rules Applied?\n\nWhen the entity is saved.\n\n## When Does a Rule Apply?\n\nWhen all the `condition` statements are `TRUE`.\n\n## Must All Condition Clauses Be True?\n\nYes, or the rule is skipped.\n\n## Must All Require Clauses Be True?\n\nYes, or the rule is in violation.\n\n## Callable Condition Explained\n\nThe trump condition is the `Drupal\\field_tag\\Rule\\Rule::CALLABLE`. Here's how you might use that. Notice the arguments may be `NULL`.  If you do not return a value, `FALSE` is assumed and the condition is considered unmet, and the rule skipped.  **Each rule may have only one callable condition.**\n\n```php\n$callable = function (\n  ?\\Drupal\\Core\\Entity\\EntityInterface $entity,\n  ?\\Drupal\\Core\\Field\\FieldItemListInterface $item_list\n): bool {\n  \/\/ Do something that returns a bool.\n};\n\n$rule = (new Drupal\\field_tag\\Rule\\Rule())\n  ->condition(Drupal\\field_tag\\Rule\\Rule::CALLABLE, $callable)\n  ->require(Drupal\\field_tag\\Rule\\Rule::TAG_VALUE, 'foo')\n  ->require(Drupal\\field_tag\\Rule\\Rule::TAG_MIN_PER_ENTITY, 1);\n```"},{"id":"events","title":"Tag-Related Events","body":"If you need to react to a tag being added or removed you can subscribe to the event(s): `\\Drupal\\field_tag\\Event\\FieldTagEvents::TAG_REMOVED` and\/or `\\Drupal\\field_tag\\Event\\FieldTagEvents::TAG_ADDED`.\n\nSee `\\Drupal\\field_tag\\Event\\TagEvent`, for the context available.\n\nHere is an example implementation:\n\n```php\nclass Foo implements \\Symfony\\Component\\EventDispatcher\\EventSubscriberInterface {\n\n  public static function getSubscribedEvents() {\n    return [\n      \\Drupal\\field_tag\\Event\\FieldTagEvents::TAG_ADDED => 'invalidateBlockCache',\n      \\Drupal\\field_tag\\Event\\FieldTagEvents::TAG_REMOVED => 'invalidateBlockCache',\n    ];\n  }\n\n  public function invalidateBlockCache(TagEvent $event) {\n\n    \/\/ If we see the \"highlights\" tag adding\/removing, we need to rebuild the\n    \/\/ highlights block (id 17), so we have to invalidate the cache tag.\n    if ($event->getFieldTag()->has('highlights') {\n      $cid = 'block_content:17';\n      $this->cacheTagsInvalidator->invalidateTags([$cid]);\n    }\n  }\n\n}\n```\n\n## Deleting Parent Entities\n\nWhen parent entities (those with field tagging enabled) are deleted, the associated field tag entities are also deleted. This also fires the `\\Drupal\\field_tag\\Event\\FieldTagEvents::TAG_REMOVED` event."},{"id":"testing","title":"Testing","body":"## Unit Tests\n\n1. From the module root.\n2. `.\/bin\/run_unit_tests.sh`\n\n## Integration Tests\n\n1. From Drupal app root.\n2. `.\/bin\/run_integration_tests.sh --filter=field_tag`\n\n## Manual Tests\n\n1. See files in _tests\/src\/Manual_"},{"id":"in_code","title":"Working Programatically with Field Tags.","body":"[See Code Examples](@code_examples)"}]
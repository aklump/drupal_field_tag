# Required Tags

## Exactly One Tag on a Specific Bundle Field

Here is the scenario:  You have a node bundle called `blog_entry`, it has `field_images`, which allows multiple images. You need to ensure that exactly one of those images is designated as the thumbnail. You've decided to use the `thumb` tag for this designation. Later your theme can cherry pick that image and render it appropriately

Here's how to write that rule:

```php
use Drupal\field_tag\Rule;

$rule = (new Rule())
  ->condition(Rule::BUNDLE, 'blog_entry')
  ->condition(Rule::HAS_FIELD, 'field_images')
  ->require(Rule::TAG_VALUE, 'thumb')
  ->require(Rule::TAG_MIN_PER_FIELD, 1)
  ->require(Rule::TAG_MAX_PER_FIELD, 1);
```

## At Least One Tag on Any Entity

Here is the scenario:  You have two fields attached to two entity types. For the node entity type it's used across multiple bundles, and you want this rule to cover all future bundles that have either of those fields; _this is pointed out to explain why the rule does not include `Rule::BUNDLE`_. However, you only want it to apply to the two current entity types, which is why you see `Rule::ENTITY`.

The reason for this rule, is that you've decided to display only some of those files in the teaser and you've designated the `teaser` tag to indicate this. You want to ensure that every `node,user` entity has at least one file between those two fields tagged `teaser`. That way you can display at least one item per node.

Here's how to write that rule:

```php
use Drupal\field_tag\Rule;

$rule = (new Rule())
  ->condition(Rule::ENTITY, ['node', 'user'], 'in')
  ->condition(Rule::HAS_FIELD, ['field_images', 'field_pdfs'], 'in')
  ->require(Rule::TAG_VALUE, 'teaser')
  ->require(Rule::TAGGED_FIELD, ['field_images', 'field_pdfs'], 'in')
  ->require(Rule::TAG_MIN_PER_ENTITY, 1);
```

Here's how to read it in plain English:

> Any node or user entity that has `field_images` and/or `field_pdfs` must have either of those fields tagged at least once with `teaser`.

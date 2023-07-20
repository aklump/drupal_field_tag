<!--
id: drupal_migrations
tags: ''
-->

# Drupal Migrations

Here's an example of how you might tag an image field during a migration, this assumes `field_images` has field tagging enabled and that 1) your source has no field tags or 2) you wish to replace those existing field tags.

```yaml
process:
  field_images:
    plugin: sub_process
    source: field_hero_images
    process:
      target_id: fid
      alt: alt
      title: title
      width: width
      height: height
      field_tag:
        plugin: default_value
        default_value: hero
```

And here is a migration where source has field tags and you wish to merge in new ones.

```yaml
process:
    # First copy over as is from source.
    field_images: field_images

    # Then add two tags to the first element only.
    field_images/0/field_tag:
      - plugin: field_tag_add
        source: field_images/0/field_tag
        field_tag: cover, card
```

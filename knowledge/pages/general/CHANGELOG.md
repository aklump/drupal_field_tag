<!--
id: changelog
tags: ''
-->

# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Todo

- When field_tag_paragraph processes an item, redirect to the node edit form to immediately begin editing.
- Replace the need for attachTags with a hook like: hook_entity_storage_load() or hook_entity_field_values_init()

## [0.4.0] - 2023-03-01

### Changed

- The `\Drupal\field_tag\Event\TagEvent` now delivers an instance of FieldTag that has only the added or removed tags. You will most likely **need to update your event handlers**. Here is an example of a diff.

```
- if ('some_tag' === $event->getTag()) {
+ if ($event->getFieldTag()->hasTag('some_tag')) {
```

### Deprecated

- `\Drupal\field_tag\FieldTagService::normalizeFieldTagValue`. Use `implode(', ', \Drupal\field_tag\Tags::create($value)->all());`
- `\Drupal\field_tag\FieldTagService::doesEntityUseFieldTags`. Use `\Drupal\field_tag\FieldTagService::getTaggedFieldDefinitionsByEntity()` cast to boolean
- `\Drupal\field_tag\FieldTagService::getFieldTagsAsArray`. Use `Tags::create($value)->all()` instead.
- `\Drupal\field_tag\Event\TagEvent::getEntity` use `\Drupal\field_tag\Event\TagEvent::getFieldTag->getParentEntity()` instead.
- `\Drupal\field_tag\Event\TagEvent::getTag` use `\Drupal\field_tag\Event\TagEvent::getFieldTag()->getValue()` instead.

### Removed

- lorem

### Fixed

- lorem

### Security

- lorem

## [0.3.0] - 2022-02-09

### Added

- Dispatch event `\Drupal\field_tag\Event\FieldTagEvents::TAG_ADDED` when a tag is added to a parent.
- Dispatch event `\Drupal\field_tag\Event\FieldTagEvents::TAG_REMOVED` when a tag is removed from a parent.

## [0.2.0] - 2022-01-18

### Added

- Field tag paragraphs module
- `FieldTag::removeTag()` method.

## [0.0.19] - 2020-04-22

### Fixed

- More bugs that might loose the field tags during certain operations.

### Added

- Manual tests for QA.

## [0.0.17] - 2020-04-16

### Fixed

- A bug that might loose the field tags for an field item, on multiple item fields, if items with lower deltas were removed via AJAX.

## [8.x-0.0.13] - 2020-03-16

### Changed

- `loadFromParent` has been replaced by `loadByParentField`.
- `\Drupal\field_tag\FieldTagService::attachTags` now sets the FieldTag instance as `$item->fieldTag` instead of `$item->field_tag`. The latter should only be used when you are setting the value, as this is what will be saved when the entity is saved.

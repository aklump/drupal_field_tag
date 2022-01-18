# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Todo

- When field_tag_paragraph processes an item, redirect to the node edit form to immediately begin editing.

## [0.2.0] - 2022-01-18

### Added

- Field tag paragraphs module
- FieldTag::removeTag() method.

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

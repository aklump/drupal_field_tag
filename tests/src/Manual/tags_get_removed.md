---
Test Case ID: tags-get-removed
Author: Aaron Klump
Created: February 28, 2023
Duration:
---

## Test Scenario

A tagged paragraph that is removed and immediately replaced without saving the entity does not retain the original tag in the replaced paragraph.

## Pre-Conditions

1. An entity type with an image field.
1. The image field has field tagging enabled.
1. The image field allows unlimited items.
2. Three random images for uploading.

## Test Data

    tag1: alpha
    tag2: bravo

## Test Execution

1. Begin adding new entity.
1. Add an image and tag it with {{ tag1 }}.
1. Add a second image and tag it with {{ tag2 }}.
1. Save entity.

- Assert the entity was indeed saved.
- Assert there are exactly two images.

1. Reload the entity edit page.
2. Remove the image tagged with {{ tag2 }} using the _Remove_ button
3. Without saving, _Add a new file_
4. Add a new image (in position two)

- Assert the new image has no tags.

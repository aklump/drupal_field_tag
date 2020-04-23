---
Test Case ID: unsaved-multi-image
Author: Aaron Klump
Created: April 21, 2020
Duration:
---
## Test Scenario

Tagging more than one image in a filefield works.

## Pre-Conditions

1. An entity type with an image field.
1. The image field has field tagging enabled.
1. The image field allows unlimited images.

## Test Data

    tag1: alpha

## Test Execution

1. Begin adding new entity.
1. Upload your first image and tag it {{ tag1 }}
1. Upload your second image
    - Assert {{ tag1 }} is still present in the first image's text field.

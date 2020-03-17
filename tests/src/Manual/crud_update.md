---
Test Case ID: crud-update
Author: Aaron Klump
Created: March 16, 2020
Duration:
---
## Test Scenario

User is able to update a field tag when saving an entity.

## Pre-Conditions

1. Create an entity that has a paragraphs field.
1. Make sure the paragraphs field has field tagging enabled.
1. Save the entity with a tag value of `alpha`

## Test Data

    value: alpha
    value2: alpha, bravo

## Test Execution

1. Load the entity edit form
  - Assert the tag field shows {{ value }}
1. Enter {{ value2 }} in the tag field and save.
1. Load the entity edit form again
  - Assert the tag field shows {{ value2 }}

![crud-update-1](images/crud-update-1.jpg)

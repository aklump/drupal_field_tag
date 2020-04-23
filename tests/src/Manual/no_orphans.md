---
Test Case ID: no-orphans
Author: Aaron Klump
Created: April 22, 2020
Duration:
---
## Test Scenario

Tagging three paragraphs, changing orders, and finally deleting one paragraph works as expecting: keeping tag/paragraph associations correct and removing the field tag entities as appropriate.

## Pre-Conditions

1. An entity type with a paragraphs field.
1. The paragraphs field has field tagging enabled.
1. The paragraphs field allows unlimited items.

## Test Data

    tag1: alpha
    tag2: bravo
    tag3: charlie

## Test Execution

1. Begin adding new entity.
1. Create a copy paragraph with {{ tag1 }} as the value and tag it with {{ tag1 }}.
1. Create another copy paragraph with {{ tag2 }} as the value and tag it with {{ tag2 }}.
1. Save entity.
1. Reload the entity edit page.
    - Assert the tags match their content.
1. Drag {{ tag2 }} before {{ tag1 }}
1. Save entity.
1. Reload the entity edit page.
    - Assert the tags match their content.
1. Create a third copy paragraph with {{ tag3 }} as the value and tag it with {{ tag3 }}.
1. Drag it to the top position.
1. Save entity.
1. Reload the entity edit page.
    - Assert all three tags match their content.
    - Assert the order is {{ tag3 }}, {{ tag2 }}, {{ tag1 }}.
1. Drag them in the original order: {{ tag1 }}, {{ tag2 }}, {{ tag3 }}.
1. Remove the paragraph with {{ tag3 }}.
1. Save entity.
1. Reload the entity edit page.
    - Assert the edit form loads.
    - Assert all two tags match their content.
1. Add a new third paragraph.
    - Assert the tag field is empty, does not contain {{ tag3 }}

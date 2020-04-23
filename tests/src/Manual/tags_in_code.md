---
Test Case ID: tags-in-code
Author: Aaron Klump
Created: April 22, 2020
Duration:
---
## Test Scenario

It is possible to tag a field when creating and updating an entity programmatically; tags get attached correctly on read; field tag entities are deleted when the parent entity is deleted.

## Pre-Conditions

1. An entity type with an image field called {{ _i_field }}
1. The image field has field tagging enabled.
1. The image field allows unlimited images.
1. An entity type with a paragraphs field called {{ _p_field }}
1. The paragraphs field has field tagging enabled.
1. The paragraphs field allows unlimited items.

## Test Data

    _nid: 18733
    _i_field: field_images
    _p_field: field_main_copy
    _banner: banner
    _vimeo: vimeo
    tag1: banner, vimeo
    tag2: thumb image
    tag3: foo bara
    ptag1: apple
    ptag2: banana

## Test Execution

1. Begin adding new entity.
1. Upload your first image and tag it {{ tag1 }}
1. Upload your second image and tag it {{ tag2 }}
1. Create a copy paragraph with {{ ptag1 }} as the value and tag it with {{ ptag1 }}.
1. Create another copy paragraph with {{ ptag2 }} as the value and tag it with {{ ptag2 }}.
1. Save the entity.
1. Run _Snippet A_ replacing `$nid` with the node ID of the entity you created.
    - Assert the output is 'passed'
1. Run _Snippet B_ replacing `$nid` with the node ID of the entity you created.
    - Assert the output is 'passed'
1. Reload the edit form for the entity.
    - Assert the image is tagged with {{ tag3 }}.
    - Assert {{ _p_field }} has the correct tags in place.
    

## Code Snippets

### Snippet A

Make sure we can programmatically access our tagged items.

    <?php
    $node = \Drupal\node\Entity\Node::load({{ _nid }});
    $service = \Drupal::service('field_tag')->attachTags($node);
    
    // Assert we can access a CSV tag.
    $vimeos = $service->getItemsTaggedBy('{{ _vimeo }}', 'field_images');
    1 === count($vimeos) || die('{{ _vimeo }} failed');
    
    // Assert we can access another CSV tag.
    $banners = $service->getItemsTaggedBy('{{ _banner }}', 'field_images');
    1 === count($banners) || die('{{ _banner }} failed');
    
    // Assert we can access a single tag with a space.
    $thumbs = $service->getItemsTaggedBy('{{ tag2 }}', 'field_images');
    1 === count($thumbs) || die('{{ tag2 }} failed');
    
    // Assert the fieldTag instance.
    // Assert the key is 1, which matches the delta of the field item.
    $result = $thumbs[1]->fieldTag instanceof \Drupal\field_tag\Entity\FieldTag;
    $result || die('no fieldTag instance');

    die('passed');

### Snippet B
    
    <?php
    $node = \Drupal\node\Entity\Node::load({{ _nid }});
    $service = \Drupal::service('field_tag')->attachTags($node);
    $i = $service->getItemsTaggedBy('{{ tag2 }}', 'field_images');
    $item = array_first($i)->getValue();
    $item['field_tag'] = '{{ tag3 }}';
    $node->field_images->filter(function () {
      return FALSE;
    })->appendItem($item);
    $node->save();
    $service = \Drupal::service('field_tag')->attachTags($node);
    
    $thumbs = $service->getItemsTaggedBy('{{ tag2 }}', 'field_images');
    0 === count($thumbs) || die('{{ tag2 }} failed');
    
    $foobars = $service->getItemsTaggedBy('{{ tag3 }}', 'field_images');
    1 === count($foobars) || die('{{ tag3 }} failed');
    
    die('passed');
    
### Snippet C
    
    <?php
    $node = \Drupal\node\Entity\Node::load({{ _nid }});
    $service = \Drupal::service('field_tag')->attachTags($node);
    $item = array_first($service->getItemsTaggedBy('{{ tag2 }}', 'field_images'))->getValue()
        
    // Assert 'field_tag' replaces when an array.
    $item = $alphas[0]->getValue();
    $item['field_tag'] = ['tag' => 'bravo charlie, delta'];
    $node->field_images->filter(function () {
      return FALSE;
    })->appendItem($item);
    $node->save();
    $service = \Drupal::service('field_tag')->attachTags($node);
    
    $thumb_images = $service->getItemsTaggedBy('{{ tag2 }}', 'field_images');
    0 === count($thumb_images) || die('{{ tag2 }} failed');
    $bravos = $service->getItemsTaggedBy('bravo charlie', 'field_images');
    1 === count($bravos) || die('bravo charlie failed');

    
    

    


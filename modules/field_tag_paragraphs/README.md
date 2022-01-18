# Field Tag Paragraphs Drupal Module

## Summary

The module allows you use the field tag input to enter "tags" that create paragraphs when saving the entity.  In effect the tags act as commands, and get removed once executed; that is to say they do not persist as field tags normally do.  It requires the paragraphs module.

For example if you have enabled field tags for `field_items`, which is a paragraphs reference field, you can--with this module--enter tags such as `add foo after` and `add foo before`, where `foo` is the machine name of a paragraph, and when the entity is saved, empty `foo` paragraph(s) will be automatically added.  You will automatically be returned to the edit for to fill out any values necessary for the new `foo` paragraphs.

Taking inspiration from [Emmet](https://www.emmet.io/), syntax can also take any of these forms: `add foo*3 before` and `add foo+bar+baz before`.  If you know Emmet you should understand what those commands will do.

## Installation

1. Download this module to _web/modules/custom/field_tag_paragraphs_.
1. Add the following to the application's _composer.json_ above web root.

    ```json
    {
      "repositories": [
        {
          "type": "path",
          "url": "web/modules/custom/field_tag_paragraphs"
        }
      ]
    }
    ```

1. Now run `composer require drupal/field-tag-paragraphs`
1. Enable this module.

## Configuration

        $config['field_tag_paragraphs.settings']['foo'] = 'bar;

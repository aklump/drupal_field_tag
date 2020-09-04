![Implementation](images/implementation.jpg)

## Summary

Provides a means to attach a tag-style input box to any entity field, which allows content managers to tag the field content.  Developers may take advantage of this additional metadata when manipulating fields.

The use case for which this was written is this.  Allow the tagging of images on an multiple value image field to indicate which image is the `hero` image, which image is the `thumb` image, and untagged images are just that.  It allows the content managers to indicate the role the image is playing for that given entity.

**Visit <https://aklump.github.io/field_tag> for full documentation.**

## About Tags

* Tags are not case-sensitive.
* Tags may contain inner spaces, but neither leading nor trailing spaces nor commas.
* Multiple tags must separated by a comma or "comma+space", e.g. 'foo,bar' or 'foo, bar'.
* When tags are saved, duplicates will be removed and spaces will be added after each comma if they were not already present.

## Configuration

1. Enable this module.
1. Visit the _Manage fields_ page for the entity you've picked.
1. Click on the _Edit_ button for the given field.
1. Enable the _Field Tag_, and adjust settings as necessary.  ![Settings](images/settings.jpg)
1. Give the permission _Use field tagging_ to the correct user roles.
1. Visit an entity edit page and make sure you see the tag field as configured.

## Developers

{% include('developers.md') %}

## Tagging During Migration

Here's an example of how you might tag an image field during a migration, this assumes field_images is already set up with field tagging and that 1) your source has no field tags or 2) you wish to replace those existing field tags.

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

And here is a migration where source has field tags and you wish to merge in new ones.
    
    process:
        # First copy over as is from source.
        field_images: field_images
        
        # Then add two tags to the first element only.
        field_images/0/field_tag:
          - plugin: field_tag_add
            source: field_images/0/field_tag
            field_tag: cover, card
          
## Contributing

If you find this project useful... please consider [making a donation](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4E5KZHDQCEUV8&item_name=Gratitude%20for%20aklump%2Ffield_tag).

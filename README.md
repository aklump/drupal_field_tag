# Field Tag Drupal Module

![Implementation](images/implementation.jpg)

## Summary

Provides a means to attach a tag-style input box to any entity field, which allows content managers to tag the field content.  Developers may take advantage of this additional metadata when manipulating fields.

The use case for which this was written is this.  Allow the tagging of images on an multiple value image field to indicate which image is the `hero` image, which image is the `thumb` image, and untagged images are just that.  It allows the content managers to indicate the role the image is playing for that given entity.

## Configuration

1. Enable this module.
1. Visit the _Manage fields_ page for the entity you've picked.
1. Click on the _Edit_ button for the given field.
1. Enable the _Field Tag_, and adjust settings as necessary.  ![Settings](images/settings.jpg)
1. Give the permission _Use field tagging_ to the correct user roles.
1. Visit an entity edit page and make sure you see the tag field as configured.

## Developers

This provides the UI and storage mechanism for field content tagging.  It creates a new entity type _field_tag_.  It's up to you to implement use cases for the data.  See _field_tag.api.php_ for example code.

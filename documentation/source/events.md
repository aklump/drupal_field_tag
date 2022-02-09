# Tag Events

If you need to react to a tag being added or removed you can subscribe to the event(s): `\Drupal\field_tag\Event\FieldTagEvents::TAG_REMOVED` and/or `\Drupal\field_tag\Event\FieldTagEvents::TAG_ADDED`.

See `\Drupal\field_tag\Event\TagEvent`, which will give you the single tag, and the parent entity.

Here is an example implementation:

```php
<?php

class Foo implements \Symfony\Component\EventDispatcher\EventSubscriberInterface {

  public static function getSubscribedEvents() {
    return [
      \Drupal\field_tag\Event\FieldTagEvents::TAG_ADDED => 'invalidateBlockCache',
      \Drupal\field_tag\Event\FieldTagEvents::TAG_REMOVED => 'invalidateBlockCache',
    ];
  }

  public function invalidateBlockCache(TagEvent $event) {

    // If we see the "highlights" tag adding/removing, we need to rebuild the
    // highlights block (id 17), so we have to invalidate the cache tag.
    if ('highlights' === $event->getTag()) {
      $cid = 'block_content:17';
      $this->cacheTagsInvalidator->invalidateTags([$cid]);
    }
  }

}
```

## Deleting Parent Entities

When parent entities (those with field tagging enabled) are deleted, the associated field tag entities are also deleted. However **this does not trigger the `TAG_REMOVED` event**. If you wish to react to that scenario then you will need to use `hook_entity_delete()`. To see how that could work look to `field_tag_entity_delete()`.

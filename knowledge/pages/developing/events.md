<!--
id: events
tags: ''
-->

# Tag-Related Events

If you need to react to a tag being added or removed you can subscribe to the event(s): `\Drupal\field_tag\Event\FieldTagEvents::TAG_REMOVED` and/or `\Drupal\field_tag\Event\FieldTagEvents::TAG_ADDED`.

See `\Drupal\field_tag\Event\TagEvent`, for the context available.

Here is an example implementation:

```php
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
    if ($event->getFieldTag()->has('highlights') {
      $cid = 'block_content:17';
      $this->cacheTagsInvalidator->invalidateTags([$cid]);
    }
  }

}
```

## Deleting Parent Entities

When parent entities (those with field tagging enabled) are deleted, the associated field tag entities are also deleted. This also fires the `\Drupal\field_tag\Event\FieldTagEvents::TAG_REMOVED` event.

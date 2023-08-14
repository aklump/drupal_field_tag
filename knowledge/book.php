<?php

/** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */

use AKlump\Knowledge\Events\AssemblePages;
use AKlump\Knowledge\Model\BookPageInterface;
use AKlump\Knowledge\Model\Page;

$dispatcher->addListener(AssemblePages::NAME, function (AssemblePages $event) {
  $content = file_get_contents(__DIR__ . '/../field_tag.api.php');
  $content = "# Code Examples\n\n```php\n$content```";
  // TODO Parse into markdown.
  $page = new Page('code_examples', 'developing');
  $page
    ->addTag('api')
    ->setBody($content, BookPageInterface::MIME_TYPE_MARKDOWN);
  $event->addPage($page);
});

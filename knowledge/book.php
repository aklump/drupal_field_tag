<?php

/** @var string $command */
/** @var string $book_path */

/** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */

use AKlump\Knowledge\Events\AssemblePages;
use AKlump\Knowledge\Model\BookPageInterface;
use AKlump\Knowledge\Model\Page;

function get_api_as_markdown(): string {
  global $book_path;
  $contents = file_get_contents($book_path . '/../field_tag.api.php');
  $contents = preg_replace('#^<\?php\n+/\*\*\n+#', '', $contents);
  $contents = array_map(fn($chunk) => "```php\n/**\n$chunk```\n\n", explode("/**\n", $contents));
  $contents = implode("\n", $contents);
  $content = "# Code Examples\n\n$contents";

  return $content;
}

$dispatcher->addListener(AssemblePages::NAME, function (AssemblePages $event) {
  $page = new Page('code_examples', 'code');
  $page
    ->addTag('api')
    ->setBody(get_api_as_markdown(), BookPageInterface::MIME_TYPE_MARKDOWN);
  $event->addPage($page);
});

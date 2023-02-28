<?php

namespace Drupal\field_tag;

use Countable;

/**
 * A Drupal-free helper class to make working with field tags easier.
 *
 * @see \Drupal\field_tag\Entity\FieldTag
 */
final class Tags implements Countable {

  const SEPARATOR = ',';

  private $tags;

  /**
   * Construct a new instance.
   *
   * To create an instance from an array:
   *
   * @code
   * $user_tags = ['do', 're', 'mi'];
   * $tags = new Tags(...$user_tags);
   * @endcode
   *
   * @param string ...$tags
   *   Any number of tags.
   */
  public function __construct(string ...$tags) {
    $this->setTags($tags);
  }

  /**
   * @return string
   *   A CSV string of all tags.
   */
  public function __toString(): string {
    return implode(self::SEPARATOR, $this->all());
  }

  /**
   * @return array
   *   All unique tags in lower-case.
   */
  public function all(): array {
    return $this->tags;
  }

  /**
   * Create a new instance using a filter.
   *
   * @param callable $callback
   *   Receives (string $tag).  Return false to remove $tag.
   *
   * @return $this
   *   A new instance with only tags that return TRUE against $callback.
   */
  public function filter(callable $callback): self {
    $tags = array_filter($this->all(), $callback);

    return new Tags(...$tags);
  }

  /**
   * Create a NEW INSTANCE of matched tags.
   *
   * @param string $pattern
   *   A regex pattern used to match the tags for inclusion, e.g. "/\d+/".
   *
   * @return \Drupal\field_tag\Tags
   *   A new instance with only the tags that match $pattern.
   */
  public function match(string $pattern): Tags {
    $matched = [];
    foreach ($this->all() as $tag) {
      if (preg_match($pattern, $tag)) {
        $matched[] = $tag;
      }
    }

    return new Tags(...$matched);
  }

  /**
   * @param string $tag
   *   A tag to be added if not already present.
   *
   * @return $this
   */
  public function add(string $tag): self {
    $tags = $this->all();
    $tags[] = $tag;
    $this->setTags($tags);

    return $this;
  }

  /**
   * Merge any number of instances with this one and return new instance.
   *
   * @param \Drupal\field_tag\Tags $tags
   *
   * @return \Drupal\field_tag\Tags
   *   A new instance
   */
  public function merge(Tags ...$tag_set): self {
    $merged = $this->all();
    foreach ($tag_set as $tags) {
      $merged = array_merge($tags->all(), $merged);
    }

    return new Tags(...$merged);
  }

  /**
   * @param array $tags
   *
   * @return void
   *
   * @throws \InvalidArgumentException
   *   If a single tag contains a comma.
   */
  private function setTags(array $tags) {
    // Sniffs out tags with commas, which is not allowed.
    foreach ($tags as $tag) {
      if (strstr($tag, self::SEPARATOR) !== FALSE) {
        throw new \InvalidArgumentException(sprintf('A single tag may not contain a "%s"; "%s" is an invalid tag.', self::SEPARATOR, $tag));
      }
    }
    $this->tags = $tags;
    $this->tags = array_map(function ($tag) {
      return trim($tag, ' ' . self::SEPARATOR);
    }, $this->tags);
    $this->tags = array_filter(array_unique($this->tags));
  }

  /**
   * @return int|null
   *   The total number of tags.
   */
  public function count(): int {
    return count($this->tags);
  }

  /**
   * Case insensitive tag matching.
   *
   * @param string $tag
   *
   * @return bool
   */
  public function has(string $tag): bool {
    $lowercase_haystack = array_map('strtolower', $this->all());

    return in_array(strtolower($tag), $lowercase_haystack);
  }

}

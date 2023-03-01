<?php

namespace Drupal\field_tag;

use Countable;

/**
 * A Drupal-free helper class to make working with field tags easier.
 *
 * @code
 * Tags::create('foo', 'bar', 'baz')->has('foo');
 * @endcode
 *
 * @see \Drupal\field_tag\Entity\FieldTag
 */
final class Tags implements Countable {

  const SEPARATOR = ',';

  private $tags;

  public static function create(?string ...$tags) {
    return new self(...$tags);
  }

  /**
   * Construct a new instance.
   *
   * To create an instance from an CSV string.  Be aware that you may only pass
   * one argument when constructing with CSV.
   *
   * @code
   * $tags = new Tags('do,re,mi');
   * @endcode
   *
   * To create an instance from an array variable use the spread operator.
   *
   * @code
   * $user_tags = ['do', 're', 'mi'];
   * $tags = new Tags(...$user_tags);
   * @endcode
   *
   * @param string ...$tags
   *   Any number of tags, or a single CSV string.
   */
  public function __construct(?string ...$tags) {
    // Normally tags may not contain self::SEPARATOR, but for our constructor we
    // allow passing a separated string.  This prevents exceptions.
    if (func_num_args() === 1) {
      $tags = explode(self::SEPARATOR, $tags[0]);
    }
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
   * Optionally receive the matched grouped values in $matches.
   *
   * @param string $pattern
   *   A regex pattern used to match the tags for inclusion, e.g. "/\d+/".
   * @param array &$matches
   *   Optional.  An array keyed by tag name, with values that are the
   *   preg_match results.  The grouping in $pattern determines the total
   *   elements in the values array, with 0 always being the full match.
   *
   * @return \Drupal\field_tag\Tags
   *   A new instance with only the tags that match $pattern.
   *
   * @see \preg_match()
   */
  public function match(string $pattern, array &$matches = []): Tags {
    $matched_tags = [];
    foreach ($this->all() as $tag) {
      if (preg_match($pattern, $tag, $found)) {
        $matched_tags[] = $tag;
        $matches[$found[0]] = $found;
      }
    }

    return new Tags(...$matched_tags);
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
  public function merge(Tags ...$tag_set): Tags {
    $merged = $this->all();
    foreach ($tag_set as $tags) {
      $merged = array_merge($tags->all(), $merged);
    }

    return new Tags(...$merged);
  }

  /**
   * Get a new instance with only unique tags not found in other instances.
   *
   * @param \Drupal\field_tag\Tags ...$tag_set
   *
   * @return \Drupal\field_tag\Tags
   *   An new instance with only those tags unique to this instance, and not
   *   present in any of $tag_set arguments.
   */
  public function diff(Tags ...$tag_set): Tags {
    $diff = array_map(function (Tags $tags) {
      return $tags->all();
    }, func_get_args());
    array_unshift($diff, $this->all());
    $diff = array_diff(...$diff);

    return new Tags(...$diff);
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
   * Get an alphabetized (ignoring case) instance
   *
   * @return \Drupal\field_tag\Tags
   *   A new instance with the tags sorted alphabetically.
   */
  public function sort(): Tags {
    $all = $this->all();
    usort($all, function ($a, $b) {
      return strcasecmp($a, $b);
    });

    return new Tags(...$all);
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

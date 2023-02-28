<?php


namespace Drupal\field_tag\Tests;

use PHPUnit\Framework\TestCase;
use Drupal\field_tag\Tags;

/**
 * @group extensions
 * @group field_tag
 * @covers \Drupal\field_tag\Tags
 */
final class TagsTest extends TestCase {

  public function testMatch() {
    $tags = new Tags('foo', 'bar', 'baz');
    $matches = $tags->match('/^f/');
    $this->assertNotSame($tags, $matches);

    $this->assertSame(1, $matches->count());
    $this->assertTrue($matches->has('foo'));

    $matches = $tags->match('/BAZ/i');
    $this->assertSame(1, $matches->count());
    $this->assertTrue($matches->has('baz'));

    $matches = $tags->match('/a{1}/');
    $this->assertSame(2, $matches->count());
    $this->assertTrue($matches->has('bar'));
    $this->assertTrue($matches->has('baz'));

    $matches = $tags->match('/\d/');
    $this->assertSame(0, $matches->count());
  }

  public function testFromArray() {
    $group = ['do', 're', 'mi'];
    $tags = new Tags(...$group);
    $this->assertContains('do', $tags->all());
    $this->assertContains('re', $tags->all());
    $this->assertContains('mi', $tags->all());
  }

  public function testLeadingAndTrailingSpaces() {
    $tags = new Tags('  lorem ', 'ipsum ', '    dolar');
    $this->assertContains('lorem', $tags->all());
    $this->assertContains('ipsum', $tags->all());
    $this->assertContains('dolar', $tags->all());
  }

  public function testWithCommas() {
    $this->expectException(\InvalidArgumentException::class);
    new Tags('grain,free', 'vegan', 'typeA,');
  }

  public function testWithSpaces() {
    $tags = new Tags('grain free', 'vegan', 'type-a');
    $this->assertCount(3, $tags);
  }

  public function testMergeMultiple() {
    $tags = new Tags('alpha');
    $merged = $tags->merge(new Tags('bravo'), new Tags('charlie'), new Tags('delta'));
    $this->assertTrue($merged->has('alpha'));
    $this->assertTrue($merged->has('bravo'));
    $this->assertTrue($merged->has('charlie'));
    $this->assertTrue($merged->has('delta'));
  }

  public function testMerge() {
    $tags = new Tags('do', 're');

    $merged = $tags->merge(new Tags('mi', 'fa'));
    $this->assertNotSame($merged, $tags);

    $this->assertContains('do', $tags->all());
    $this->assertContains('re', $tags->all());
    $this->assertNotContains('mi', $tags->all());
    $this->assertNotContains('fa', $tags->all());

    $this->assertContains('mi', $merged->all());
    $this->assertContains('fa', $merged->all());
  }

  public function testHas() {
    $tags = new Tags('foo', 'bar', 'baz');
    $this->assertTrue($tags->has('foo'));
    $this->assertTrue($tags->has('FOO'));
    $this->assertTrue($tags->has('foO'));
  }

  public function testCaseIsNotMutated() {
    $tags = new Tags('FOO');
    $this->assertTrue($tags->has('foo'));
    $this->assertSame('FOO', strval($tags));
    $this->assertContains('FOO', $tags->all());
  }

  public function testCount() {
    $tags = new Tags('foo', 'bar', 'baz');
    $this->assertSame(3, $tags->count());
  }

  public function testAdd() {
    $tags = new Tags();
    $result = $tags->add('do')->add('re');
    $this->assertSame($tags, $result);
    $this->assertContains('do', $tags->all());
    $this->assertContains('re', $tags->all());
  }

  public function testFilter() {
    $tags = new Tags('foo', 'bar', 'baz');
    $filtered = $tags->filter(function (string $tag) {
      return 'bar' !== $tag;
    });
    $this->assertNotSame($tags, $filtered);

    $filtered_set = $filtered->all();
    $this->assertContains('foo', $filtered_set);
    $this->assertNotContains('bar', $filtered_set);
    $this->assertContains('baz', $filtered_set);

  }

  public function testToString() {
    $tags = new Tags('foo', 'bar');
    $this->assertSame('foo,bar', strval($tags));
  }

  public function testConstructor() {
    $tags = new Tags();
    $this->assertSame([], $tags->all());
    $tags = new Tags('foo', 'bar');
    $this->assertContains('foo', $tags->all());
    $this->assertContains('bar', $tags->all());
  }

  protected function setUp(): void {

  }

}

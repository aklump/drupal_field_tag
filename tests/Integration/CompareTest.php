<?php

namespace Drupal\Tests\field_tag\Integration;

use Drupal\field_tag\Entity\FieldTag;
use Drupal\field_tag\Helpers\Compare;
use Drupal\paragraphs\ParagraphInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Drupal\field_tag\Helpers\Compare
 */
class CompareTest extends TestCase {

  /**
   * Provides data for test__invoke.
   */
  public function dataForTest__invokeProvider() {
    $basis = [
      'getParentEntity' => $this->createConfiguredMock(ParagraphInterface::class, [
        'getEntityTypeId' => 'paragraph',
        'id' => 123,
      ]),
      'getFieldName' => 'field_main',
      'getDelta' => 0,
      'getValue' => 'lorem, ipsum',
    ];
    $tests = [];
    $tests[] = [
      TRUE,
      $basis,
      $basis,
    ];
    $tests[] = [
      FALSE,
      [
        'getParentEntity' => $this->createConfiguredMock(ParagraphInterface::class, [
          'getEntityTypeId' => 'node',
          'id' => 123,
        ]),
      ] + $basis,
      $basis,
    ];
    $tests[] = [
      FALSE,
      [
        'getParentEntity' => $this->createConfiguredMock(ParagraphInterface::class, [
          'getEntityTypeId' => 'node',
          'id' => 234,
        ]),
      ] + $basis,
      $basis,
    ];
    $tests[] = [
      FALSE,
      ['getFieldName' => 'field_secondary'] + $basis,
      $basis,
    ];
    $tests[] = [
      FALSE,
      ['getDelta' => 1] + $basis,
      $basis,
    ];
    $tests[] = [
      FALSE,
      ['getValue' => 'foo, bar'] + $basis,
      $basis,
    ];

    return $tests;
  }

  /**
   * @dataProvider dataForTest__invokeProvider
   */
  public function testInvokeReturnsExpectedComparisonResult(bool $control, array $value1, array $value2) {
    $a = $this->createConfiguredMock(FieldTag::class, $value1);
    $b = $this->createConfiguredMock(FieldTag::class, $value2);

    $comparator = new Compare();
    $result = $comparator($a, $b);
    $this->assertSame($control, $result);
  }

  protected function setUp(): void {
    $container = new \Drupal\Core\DependencyInjection\ContainerBuilder();
    \Drupal::setContainer($container);
  }

}

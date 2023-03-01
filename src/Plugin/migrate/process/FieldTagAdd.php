<?php

namespace Drupal\field_tag\Plugin\migrate\process;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\field_tag\FieldTagService;
use Drupal\field_tag\Tags;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add field tags..
 *
 * Available configuration keys:
 * - source: Source property.
 * - field_tag: A CSV string of new tags to add to existing.
 *
 * The field_tag_add plugin returns the value of existing field tags, plus the
 * new ones you are adding as a CSV string, normalized and deduped.
 *
 * @MigrateProcessPlugin(
 *   id = "field_tag_add"
 * )
 *
 * Examples:
 *
 * This example will add the two tags, "cover" and "card" to the field tags
 *   that already exist, if any.  Duplicates are automatically removed, so this
 *   is really all you need as a plugin.
 *
 * @code
 * process:
 *   field_images/0/field_tag:
 *     - plugin: field_tag_add
 *       source: field_images/0/field_tag
 *       field_tag: cover, card
 * @endcode
 */
class FieldTagAdd extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\field_tag\FieldTagService
   */
  protected $fieldTagService;

  /**
   * FieldTagAdd constructor.
   *
   * @param \Drupal\field_tag\FieldTagService $field_tag_service
   *   A service instance.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, FieldTagService $field_tag_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fieldTagService = $field_tag_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('field_tag'));
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (empty($this->configuration['field_tag'])) {
      throw new MigrateException('Missing configuration key "field_tag"');
    }

    return strval(Tags::create($value . ',' . $this->configuration['field_tag']));
  }

}

<?php
/**
 * Allow modules to remap tags to bundles as necessary.
 */
function hook_field_tag_paragraphs_bundle_alter(string &$bundle, string $field_tag) {

  // As you will see in the my_module_entity_presave example below we might pass a
  // pixel value in addition to the bundle name; since the bundle name cannot
  // contain spaces, this preg_match will separate the bundle name from the meta
  // information, i.e., pixels.
  if (preg_match('/([^ ]+)/', $bundle, $matches)) {
    switch ($matches[1]) {

      // In this case "container" is not a valid bundle, but we want the content
      // creators to use the field tag "add container" and NOT "add
      // layout_region", which is less memorable.  This case statement maps
      // the desired name "container" to the correct bundle "layout_region".
      case 'container':
        $bundle = 'layout_region';
        break;
      default:
        $bundle = $matches[1];
        break;
    }
  }
}

/**
 * Implements hook_entity_presave().
 */
function my_module_entity_presave(\Drupal\Core\Entity\EntityInterface $entity) {

  // Context is set on the entity by this module which contains the original
  // field tag used.  This will allow you as module developer to provide extra
  // functionality.  In this case the tag has an embedded pixel value that can
  // be extracted and moved to the appropriate field on the entity.  This makes
  // the field tag paragraphs module even that much more of a time saver.
  $info = $entity->_field_tag_paragraphs ?? [];

  // The original tag used is set as $info['field_tag'], e.g. 'add spacer 32
  // after'.  This next line will extract the pixel value, i.e., 32 and set it
  // on the correct field_css_gap.
  if ($info && preg_match('/spacer (\d+)/i', $info['field_tag'], $matches)) {
    $entity->field_css_gap->value = $matches[1];
  }
}


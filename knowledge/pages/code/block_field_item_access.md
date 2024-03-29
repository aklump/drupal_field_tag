# Block Field Item Access by Tags

This examples uses tags `survey` and `no survey` to hide and show field items on a block based on the presence of an active survey.

```php
/**
 * Implements hook_block_view_alter().
 */
function my_module_block_view_alter(array &$build, \Drupal\Core\Block\BlockPluginInterface $block) {
  // Only target this one block where we expect these tags to be used.
  if ($block->getPluginId() === 'block_content:fc56a3f4-9750-4650-9274-5bf8088a6fb9') {
    $build['#pre_render'][] = [
      FieldTagPreRender::class,
      'processBlockContent',
    ];
  }
}
```

```php
<?php

namespace Drupal\my_module\Surveys;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\my_module\FooBar;

class FieldTagPreRender implements TrustedCallbackInterface {

  public static function trustedCallbacks() {
    return ['processBlockContent'];
  }

  /**
   * @param array $build
   *
   * @return array
   *
   * @see my_module_block_view_alter
   */
  public static function processBlockContent(array $build) {
    $entity = $build['content']['#block_content'];
    $field_name = 'field_sections';
    if ($entity->hasField($field_name)) {
      (new self())->processSurveyNoSurveyTags($entity, $field_name, $build['content']);
    }

    return $build;
  }

  /**
   * Handles #access for tags "survey" and "no survey"
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param string $field_name
   * @param array $base
   *
   * @return void
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function processSurveyNoSurveyTags(EntityInterface $entity, string $field_name, array &$base) {
    /** @var \Drupal\field_tag\FieldTagService $service */
    $service = \Drupal::service('field_tag');
    $service->attachTags($entity);

    $tagged_survey = $service->getItemsTaggedBy('survey', $field_name);
    $tagged_no_survey = $service->getItemsTaggedBy('no survey', $field_name);
    if (empty($tagged_survey) && empty($tagged_no_survey)) {
      return;
    }
    $survey_found = (bool) FooBar::surveyResponses()->getCurrentSurvey();
    foreach ($tagged_survey as $item) {
      $base[$field_name][$item->fieldTag->getDelta()]['#access'] = $survey_found;
    }
    foreach ($tagged_no_survey as $item) {
      $base[$field_name][$item->fieldTag->getDelta()]['#access'] = !$survey_found;
    }
  }

}
```

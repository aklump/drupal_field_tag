<?php

namespace AKlump\Knowledge\User;

use AKlump\Knowledge\Events\GetVariables;

/**
 * This creates the markdown for how to install with composer.
 *
 * 1. Add this to an event handler:
 *
 * @code
 * $dispatcher->addListener(\AKlump\Knowledge\Events\GetVariables::NAME, function (\AKlump\Knowledge\Events\GetVariables $event) {
 *   (new \AKlump\Knowledge\User\InstallWithComposerVariable())($event);
 * });
 * @endcode
 *
 * 1. Add `github_url` to variables.yml
 * 1. Add the following to your README.md file:
 *
 * @code
 * {{ composer_install|raw }}
 * @endcode
 *
 * 1. Or cherry pick these variables:
 *
 * @code
 * {{ github_url }}
 * {{ composer_require }}
 * @endcode
 */
final class InstallWithComposerVariable {

  private GetVariables $event;

  public function __invoke(GetVariables $event) {
    $this->event = $event;

    $variables = $this->event->getVariables();
    if (empty($variables['github_url'])) {
      throw new \RuntimeException(sprintf('Add "github_url" to variables.yml',));
    }

    $version = $this->getVersion();
    if (empty($version)) {
      $version = '@dev';
    }
    else {
      preg_match('/^\d+\.\d+/', $version, $matches);
      $version = $matches[0] ?? $version;
      $version = "^$version";
    }
    $composer_require = sprintf('%s:%s', $this->getPackageName(), $version);
    $event->setVariable('composer_require', $composer_require);
    $install_directions = str_replace([
      '{{ github_url }}',
      '{{ composer_require }}',
    ], [
      $variables['github_url'],
      $composer_require,
    ], $this->getTemplate());
    $event->setVariable('composer_install', $install_directions);
  }

  /**
   * @return string
   *   The full semantic version string.
   */
  private function getVersion(): string {
    $version = $this->event->getVariables()['version'] ?? '';
    preg_match('/[\d\.]+$/', $version, $matches);

    return $matches[0] ?? $version;
  }

  private function getPackageName(): string {
    $composer_json = $this->event->getPathToSource() . '/../composer.json';
    $data = json_decode(file_get_contents($composer_json), TRUE);

    return $data['name'] ?? '';
  }

  private function getTemplate(): string {
    return /** @lang markdown */ <<<EOD
    ## Install with Composer
    
    1. Because this is an unpublished package, you must define it's repository in your project's _composer.json_ file. Add the following to _composer.json_:

        ```json
        "repositories": [
            {
                "type": "github",
                "url": "{{ github_url }}"
            }
        ]
        ```

    1. Then `composer require {{ composer_require }}`    
    EOD;
  }

}

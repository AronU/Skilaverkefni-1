<?php
namespace Drupal\music_module\Controller;
use Drupal\Core\Controller\ControllerBase;
/**
 * Controller for the salutation message.
 */
class MusicModuleController extends ControllerBase {
  /**
   * Music Module.
   *
   * @return array
   *   Our message.
   */
  public function musicModule() {
    return [
      '#markup' => $this->t('Hello World'),
    ];
  }
}

<?php

namespace Drupal\farm_quick\Traits;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Provides methods for building common quick form elements.
 */
trait QuickFormElementsTrait {

  use StringTranslationTrait;

  /**
   * Current user object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Build a standard timestamp element.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $title
   *   The translated form element title string. Defaults to "Date".
   * @param bool $required
   *   Whether the timestamp is required. Defaults to TRUE.
   * @param \Drupal\Core\Datetime\DrupalDateTime|null $default
   *   The default value, as a DrupalDateTime object. Defaults to midnight of
   *   today's date in the current user's timezone.
   *
   * @return array
   *   Returns a form element array.
   */
  public function timestampElement(?TranslatableMarkup $title = NULL, bool $required = TRUE, ?DrupalDateTime $default = NULL) {
    if (is_null($title)) {
      $title = $this->t('Date');
    }
    if (is_null($default)) {
      $default = new DrupalDateTime('midnight', $this->currentUser->getTimeZone());
    }
    return [
      '#type' => 'datetime',
      '#title' => $title,
      '#required' => $required,

      '#default_value' => $default,
    ];
  }

  /**
   * Build an inline container element.
   *
   * @return array
   *   Returns a render array.
   */
  public function buildInlineContainer() {
    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'inline-container',
        ],
      ],
    ];
  }

}

<?php

namespace Drupal\farm_quick\Traits;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityInterface;
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
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   * Build a standard asset reference element.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $title
   *   The translated form element title string. Defaults to "Assets" if
   *   $multiple is TRUE, and "Asset" if $multiple is FALSE.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $description
   *   The translated form element description string.
   * @param bool $required
   *   Whether the timestamp is required. Defaults to FALSE.
   * @param bool $multiple
   *   Whether multiple values are allowed. Defaults to FALSE.
   * @param array|null $default
   *   The default value. This should be an array of asset entities.
   * @param string|null $view
   *   Use a View to filter allowed assets. This must be formatted as a string
   *   containing the View ID and display name separated by a comma. For
   *   example: my_view:my_display
   *
   * @return array
   *   Returns a form element array.
   */
  public function assetReferenceElement(?TranslatableMarkup $title = NULL, ?TranslatableMarkup $description = NULL, bool $required = FALSE, bool $multiple = FALSE, ?array $default = NULL, ?string $view = NULL) {
    if (is_null($title)) {
      $count = $multiple ? 2 : 1;
      $title = $this->formatPlural($count, 'Asset', 'Assets');
    }
    $element = [
      '#type' => 'entity_autocomplete',
      '#title' => $title,
      '#description' => $description,
      '#target_type' => 'asset',
      '#selection_settings' => [
        'sort' => [
          'field' => 'status',
          'direction' => 'ASC',
        ],
      ],
      '#maxlength' => 1024,
      '#tags' => $multiple,
      '#required' => $required,
      '#default_value' => $default,
    ];
    if (!is_null($view)) {
      $view = explode(':', $view);
      if (!empty($view[0]) && !empty($view[1])) {
        $view_name = $view[0];
        $display_name = $view[1];
        $element['#selection_handler'] = 'views';
        $element['#selection_settings'] = [
          'view' => [
            'view_name' => $view_name,
            'display_name' => $display_name,
            'arguments' => [],
          ],
          'match_operator' => 'CONTAINS',
        ];
      }
    }
    return $element;
  }

  /**
   * Load assets from an asset reference field.
   *
   * @param array|null $values
   *   The values from $form_state->getValue().
   *
   * @return \Drupal\asset\Entity\AssetInterface[]
   *   Returns an array of assets.
   */
  public function loadReferencedAssets(?array $values) {
    $entities = [];
    if (empty($values)) {
      return $entities;
    }
    if (!is_array($values)) {
      $values = [$values];
    }
    foreach ($values as $value) {
      if ($value instanceof EntityInterface) {
        $entities[] = $value;
      }
      else {
        $entity_id = NULL;
        if (is_numeric($value)) {
          $entity_id = $value;
        }
        elseif (!empty($value['target_id'])) {
          $entity_id = $value['target_id'];
        }
        if (!is_null($entity_id)) {
          $entities[] = $this->entityTypeManager->getStorage('asset')->load($entity_id);
        }
      }
    }
    return $entities;
  }

  /**
   * Build a standard collapsible notes element.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $title
   *   The translated form element title string. Defaults to "Notes".
   *
   * @return array
   *   Returns a form element array.
   */
  public function notesElement(?TranslatableMarkup $title = NULL) {
    if (is_null($title)) {
      $title = $this->t('Notes');
    }
    return [
      '#type' => 'details',
      '#title' => $title,
      'notes' => [
        '#type' => 'text_format',
        '#title' => $title,
        '#format' => 'default',
      ],
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

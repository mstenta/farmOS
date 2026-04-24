<?php

declare(strict_types=1);

namespace Drupal\farm_ui_theme\Hook;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Entity\Display\EntityFormDisplayInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\asset\Entity\AssetInterface;
use Drupal\farm_ui_theme\FarmUiThemeHelper;
use Drupal\organization\Entity\OrganizationInterface;
use Drupal\plan\Entity\PlanInterface;

/**
 * Theme hook implementations for farm_ui_theme.
 */
class ThemeHooks {

  public function __construct(
    protected ModuleExtensionList $moduleExtensionList,
    protected ModuleHandlerInterface $moduleHandler,
  ) {}

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme($existing, $type, $theme, $path) {
    return [
      'html__asset__map_popup' => [
        'base hook' => 'html',
      ],
      'menu_local_tasks__farm' => [
        'base hook' => 'menu_local_tasks',
      ],
      'menu_local_task__secondary' => [
        'base hook' => 'menu_local_task',
      ],
      'page__asset__map_popup' => [
        'base hook' => 'page',
      ],

      // Implement the node edit form theme hook.
      // See https://www.drupal.org/project/gin/issues/3342164
      'node_edit_form' => [
        'render element' => 'form',
      ],
    ];
  }

  /**
   * Implements hook_theme_registry_alter().
   */
  #[Hook('theme_registry_alter')]
  public function themeRegistryAlter(&$theme_registry) {
    $theme_registry['comment']['path'] = $this->moduleExtensionList->getPath('farm_ui_theme') . '/templates';
  }

  /**
   * Implements hook_theme_suggestions_HOOK().
   */
  #[Hook('theme_suggestions_menu_local_task')]
  public function themeSuggestionsMenuLocalTask(array $variables) {

    // Add suggestions for primary and secondary task levels.
    $suggestions = [];
    if (isset($variables['element']['#level'])) {
      $suggestions[] = 'menu_local_task__' . $variables['element']['#level'];
    }
    return $suggestions;
  }

  /**
   * Implements hook_theme_suggestions_HOOK().
   */
  #[Hook('theme_suggestions_menu_local_tasks')]
  public function themeSuggestionsMenuLocalTasks(array $variables) {
    return [
      'menu_local_tasks__farm',
    ];
  }

  /**
   * Implements hook_preprocess_HOOK().
   */
  #[Hook('preprocess_block')]
  public function preprocessBlock(&$variables) {
    if ($variables['plugin_id'] == 'help_block') {
      $variables['#attached']['library'][] = 'farm_ui_theme/help';
    }
  }

  /**
   * Implements hook_preprocess_HOOK().
   */
  #[Hook('preprocess_field')]
  public function preprocessField(&$variables) {
    if ($variables['field_type'] == 'comment') {
      $variables['attributes']['class'][] = 'gin-layer-wrapper';
    }
  }

  /**
   * Implements hook_preprocess_HOOK().
   */
  #[Hook('preprocess_field__flag')]
  public function preprocessFieldFlag(array &$variables) {
    $variables['#attached']['library'][] = 'farm_ui_theme/flag';
  }

  /**
   * Implements hook_preprocess_HOOK().
   */
  #[Hook('preprocess_field__image')]
  public function preprocessFieldImage(array &$variables) {
    $variables['#attached']['library'][] = 'farm_ui_theme/image';
  }

  /**
   * Implements hook_preprocess_HOOK().
   */
  #[Hook('preprocess_asset__full')]
  public function preprocessAssetFull(&$variables) {
    if (!empty($variables['asset']) && $variables['asset'] instanceof AssetInterface) {
      FarmUiThemeHelper::setArchivedMessage($variables['asset']);
    }
    FarmUiThemeHelper::buildStackedTwocolLayout($variables, 'asset');
    $variables['#attached']['library'][] = 'farm_ui_theme/layout';
  }

  /**
   * Implements hook_preprocess_HOOK().
   */
  #[Hook('preprocess_log__full')]
  public function preprocessLogFull(&$variables) {
    FarmUiThemeHelper::buildStackedTwocolLayout($variables, 'log');
    $variables['#attached']['library'][] = 'farm_ui_theme/layout';
  }

  /**
   * Implements hook_preprocess_HOOK().
   */
  #[Hook('preprocess_organization__full')]
  public function preprocessOrganizationFull(&$variables) {
    if (!empty($variables['organization']) && $variables['organization'] instanceof OrganizationInterface) {
      FarmUiThemeHelper::setArchivedMessage($variables['organization']);
    }
    FarmUiThemeHelper::buildStackedTwocolLayout($variables, 'organization');
    $variables['#attached']['library'][] = 'farm_ui_theme/layout';
  }

  /**
   * Implements hook_preprocess_HOOK().
   */
  #[Hook('preprocess_plan__full')]
  public function preprocessPlanFull(&$variables) {
    if (!empty($variables['plan']) && $variables['plan'] instanceof PlanInterface) {
      FarmUiThemeHelper::setArchivedMessage($variables['plan']);
    }
    FarmUiThemeHelper::buildStackedTwocolLayout($variables, 'plan');
    $variables['#attached']['library'][] = 'farm_ui_theme/layout';
  }

  /**
   * Implements hook_preprocess_HOOK().
   */
  #[Hook('preprocess_page')]
  public function preprocessPage(&$variables) {
    $variables['#attached']['library'][] = 'farm_ui_theme/regions';
  }

  /**
   * Implements hook_preprocess_HOOK().
   */
  #[Hook('preprocess_page__dashboard')]
  public function preprocessPageDashboard(&$variables) {
    $variables['#attached']['library'][] = 'farm_ui_theme/dashboard';
  }

  /**
   * Implements hook_preprocess_HOOK().
   */
  #[Hook('preprocess_views_view')]
  public function preprocessViewsView(&$variables) {
    $variables['#attached']['library'][] = 'farm_ui_theme/views';
  }

  /**
   * Implements hook_entity_form_display_alter().
   */
  #[Hook('entity_form_display_alter')]
  public function entityFormDisplayAlter(EntityFormDisplayInterface $form_display, array $context) {

    // Only alter farm entity types.
    $entity_types = [
      'asset',
      'log',
      'organization',
      'plan',
      'taxonomy_term',
    ];
    if (!in_array($context['entity_type'], $entity_types)) {
      return;
    }

    // Ask modules for a list of field group items.
    $field_map = $this->moduleHandler->invokeAll('farm_ui_theme_field_group_items', [
      $context['entity_type'],
      $context['bundle'],
    ]);

    // Apply the field group mapping if not already specified on the form
    // display.
    foreach ($field_map as $field_id => $field_group) {
      if (($renderer = $form_display->getRenderer($field_id)) && !$renderer->getThirdPartySetting('farm_ui_theme', 'field_group', FALSE)) {
        $renderer->setThirdPartySetting('farm_ui_theme', 'field_group', $field_group);
      }
    }
  }

  /**
   * Implements hook_gin_content_form_routes().
   */
  #[Hook('gin_content_form_routes')]
  public function ginContentFormRoutes() {
    $routes = [];
    $entity_types = [
      'asset',
      'log',
      'organization',
      'plan',
      'taxonomy_term',
    ];
    foreach ($entity_types as $entity_type) {
      $routes[] = "entity.{$entity_type}.add_form";
      $routes[] = "entity.{$entity_type}.edit_form";
    }
    return $routes;
  }

  /**
   * Implements hook_element_info_alter().
   */
  #[Hook('element_info_alter')]
  public function elementInfoAlter(array &$info) {
    if (isset($info['farm_map'])) {
      $info['farm_map']['#attached']['library'][] = 'farm_ui_theme/map';
    }
  }

  /**
   * Implements hook_block_view_BASE_BLOCK_ID_alter().
   */
  #[Hook('block_view_farm_powered_by_block_alter')]
  public function blockViewFarmPoweredByBlockAlter(array &$build, BlockPluginInterface $block) {
    $build['#attached']['library'][] = 'farm_ui_theme/footer';
  }

  /**
   * Implements hook_farm_ui_theme_field_group_items().
   */
  #[Hook('farm_ui_theme_field_group_items')]
  public function farmUiThemeFieldGroupItems(string $entity_type, string $bundle) {

    // Define base fields for asset, log, and plans on behalf of core modules.
    $fields = [
      'name' => 'default',
      'status' => 'meta',
      'flag' => 'meta',
      'file' => 'file',
      'image' => 'file',
      'revision' => 'revision',
      'revision_log_message' => 'revision',
    ];
    switch ($entity_type) {
      case 'asset':
        $fields['owner'] = 'meta';
        $fields['parent'] = 'parent';
        $fields['intrinsic_geometry'] = 'location';
        $fields['is_location'] = 'location';
        $fields['is_fixed'] = 'location';
        $fields['id_tag'] = 'id_tag';
        $fields['archived'] = 'meta';
        break;

      case 'log':
        $fields['timestamp'] = 'default';
        $fields['category'] = 'meta';
        $fields['owner'] = 'meta';
        $fields['asset'] = 'asset';
        $fields['geometry'] = 'location';
        $fields['location'] = 'location';
        $fields['is_movement'] = 'location';
        $fields['quantity'] = 'quantity';
        break;

      case 'organization':
        $fields['archived'] = 'meta';
        break;

      case 'plan':
        $fields['owner'] = 'meta';
        $fields['archived'] = 'meta';
        break;

      case 'taxonomy_term':
        $fields['external_uri'] = 'reference';
        break;

      default:
        $fields = [];
    }
    return $fields;
  }

  /**
   * Implements hook_farm_ui_theme_region_items().
   */
  #[Hook('farm_ui_theme_region_items')]
  public function farmUiThemeRegionItems(string $entity_type) {

    // Define common asset, log, and plan region items on behalf of core
    // modules.
    switch ($entity_type) {
      case 'asset':
        return [
          'top' => [
            'geometry',
          ],
          'first' => [],
          'second' => [
            'inventory',
            'is_location',
            'is_fixed',
            'location',
            'owner',
            'type',
            'archived',
          ],
          'bottom' => [
            'api',
            'image',
            'file',
          ],
        ];

      case 'log':
        return [
          'top' => [
            'geometry',
          ],
          'first' => [],
          'second' => [
            'is_movement',
            'owner',
            'status',
            'type',
          ],
          'bottom' => [
            'image',
            'file',
          ],
        ];

      case 'plan':
        return [
          'top' => [],
          'first' => [],
          'second' => [
            'status',
            'owner',
            'type',
            'archived',
          ],
          'bottom' => [
            'image',
            'file',
          ],
        ];

      default:
        return [];
    }
  }

}

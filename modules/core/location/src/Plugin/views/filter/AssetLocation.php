<?php

namespace Drupal\farm_location\Plugin\views\filter;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\farm_location\AssetLocationInterface;
use Drupal\views\Plugin\views\filter\ManyToOne;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A handler for filtering assets by their current location.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("asset_location")
 */
class AssetLocation extends ManyToOne {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The asset location service.
   *
   * @var \Drupal\farm_location\AssetLocationInterface
   */
  protected $assetLocation;

  /**
   * Constructs an AssetLocation object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\farm_location\AssetLocationInterface $asset_location
   *   The asset location service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, AssetLocationInterface $asset_location) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->assetLocation = $asset_location;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('asset.location'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function operatorOptions($which = 'title') {

    // Only allow the 'IN' operator, because our query() method logic does not
    // currently support other operators.
    return array_filter(parent::operatorOptions(), function($k) {
      return $k == 'in';
    }, ARRAY_FILTER_USE_KEY);
  }

  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    $default_value = [];
    if (!empty($this->value)) {
      $default_value = $this->entityTypeManager->getStorage('asset')->loadMultiple((array) $this->value);
    }
    $form['value'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->valueTitle,
      '#default_value' => $default_value,
      '#target_type' => 'asset',
      '#selection_handler' => 'views',
      '#selection_settings' => [
        'view' => [
          'view_name' => 'farm_location_reference',
          'display_name' => 'entity_reference',
        ],
        'match_operator' => 'CONTAINS',
        'match_limit' => 10,
      ],
      '#tags' => TRUE,
      '#validate_reference' => FALSE,
      '#maxlength' => 1024,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getValueOptions() {
    if (isset($this->valueOptions)) {
      return $this->valueOptions;
    }

    // @todo populate this with real values
    // @todo render location hierarchy
    $this->valueOptions = [
      2 => 'Greenhouse 1',
      53 => 'Field C',
    ];

    return $this->valueOptions;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {

    // First query for a list of asset IDs in the location, then use this list
    // to filter the current View.
    // We do this in two separate queries for a few reasons:
    // 1. The Drupal and Views query APIs do not support the kind of compound
    // JOIN that we use in the asset.location service's getAssetsByLocation().
    // 2. We need to allow other modules to override the asset.location service
    // to provide their own location logic (eg: the Group asset module). They
    // shouldn't have to override this Views argument handler as well.
    // 3. It keeps this Views argument handler's query modifications very
    // simple. It only needs the condition: "WHERE asset.id IN (:asset_ids)".
    // See https://www.drupal.org/project/farm/issues/3217168 for more info.
    // This same approach is used in argument/AssetLocation.php.
    $asset_ids = [];
    if (!empty($this->value)) {
      foreach ($this->value as $value) {
        /** @var \Drupal\asset\Entity\AssetInterface $location */
        $location = $this->entityTypeManager->getStorage('asset')->load($value['target_id']);
        $assets = $this->assetLocation->getAssetsByLocation([$location]);
        foreach ($assets as $asset) {
          $asset_ids[] = $asset->id();
        }
      }
    }

    // Set $this->value to the asset IDs in the selected location(s).
    $this->value = $asset_ids;

    // Delegate to the parent method.
    parent::query();
  }

}

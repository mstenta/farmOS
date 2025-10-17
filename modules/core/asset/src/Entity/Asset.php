<?php

declare(strict_types=1);

namespace Drupal\asset\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Entity\Form\DeleteMultipleForm;
use Drupal\Core\Entity\RevisionLogEntityTrait;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\asset\AssetListBuilder;
use Drupal\asset\AssetStorage;
use Drupal\asset\Form\AssetForm;
use Drupal\entity\Menu\DefaultEntityLocalTaskProvider;
use Drupal\entity\QueryAccess\UncacheableQueryAccessHandler;
use Drupal\entity\Revision\RevisionableContentEntityBase;
use Drupal\entity\Routing\AdminHtmlRouteProvider;
use Drupal\entity\Routing\RevisionRouteProvider;
use Drupal\entity\UncacheableEntityAccessControlHandler;
use Drupal\entity\UncacheableEntityPermissionProvider;
use Drupal\user\EntityOwnerTrait;
use Drupal\views\EntityViewsData;

/**
 * Defines the asset entity.
 *
 * @ingroup asset
 */
#[ContentEntityType(
  id: 'asset',
  label: new TranslatableMarkup('Asset'),
  label_collection: new TranslatableMarkup('Assets'),
  label_singular: new TranslatableMarkup('asset'),
  label_plural: new TranslatableMarkup('assets'),
  entity_keys: [
    'id' => 'id',
    'revision' => 'revision_id',
    'bundle' => 'type',
    'label' => 'name',
    'owner' => 'uid',
    'uuid' => 'uuid',
    'langcode' => 'langcode',
  ],
  handlers: [
    'storage' => AssetStorage::class,
    'access' => UncacheableEntityAccessControlHandler::class,
    'list_builder' => AssetListBuilder::class,
    'permission_provider' => UncacheableEntityPermissionProvider::class,
    'query_access' => UncacheableQueryAccessHandler::class,
    'view_builder' => EntityViewBuilder::class,
    'views_data' => EntityViewsData::class,
    'form' => [
      'add' => AssetForm::class,
      'edit' => AssetForm::class,
      'delete' => ContentEntityDeleteForm::class,
      'delete-multiple-confirm' => DeleteMultipleForm::class,
    ],
    'route_provider' => [
      'default' => AdminHtmlRouteProvider::class,
      'revision' => RevisionRouteProvider::class,
    ],
    'local_task_provider' => [
      'default' => DefaultEntityLocalTaskProvider::class,
    ],
  ],
  links: [
    'canonical' => '/asset/{asset}',
    'add-page' => '/asset/add',
    'add-form' => '/asset/add/{asset_type}',
    'delete-form' => '/asset/{asset}/delete',
    'delete-multiple-form' => '/asset/delete',
    'edit-form' => '/asset/{asset}/edit',
    'revision' => '/asset/{asset}/revisions/{asset_revision}/view',
    'revision-revert-form' => '/asset/{asset}/revisions/{asset_revision}/revert',
    'version-history' => '/asset/{asset}/revisions',
  ],
  admin_permission: 'administer assets',
  collection_permission: 'access asset collection',
  permission_granularity: 'bundle',
  bundle_entity_type: 'asset_type',
  bundle_label: new TranslatableMarkup('Asset type'),
  base_table: 'asset',
  data_table: 'asset_field_data',
  revision_table: 'asset_revision',
  translatable: TRUE,
  show_revision_ui: TRUE,
  label_count: [
    'singular' => '@count asset',
    'plural' => '@count assets',
  ],
  field_ui_base_route: 'entity.asset_type.edit_form',
  common_reference_target: TRUE,
  revision_metadata_keys: [
    'revision_user' => 'revision_user',
    'revision_created' => 'revision_created',
    'revision_log_message' => 'revision_log_message',
  ],
)]
class Asset extends RevisionableContentEntityBase implements AssetInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;
  use RevisionLogEntityTrait;

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->getName();
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBundleLabel() {
    /** @var \Drupal\asset\Entity\AssetTypeInterface $type */
    $type = $this->entityTypeManager()
      ->getStorage('asset_type')
      ->load($this->bundle());
    return $type->label();
  }

  /**
   * {@inheritdoc}
   */
  public static function getCurrentUserId() {
    return [\Drupal::currentUser()->id()];
  }

  /**
   * {@inheritdoc}
   */
  public static function getRequestTime() {
    return \Drupal::time()->getRequestTime();
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);
    $fields += static::revisionLogBaseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the asset.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setSetting('text_processing', 0)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the asset.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\asset\Entity\Asset::getCurrentUserId')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 12,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the asset was created.'))
      ->setRevisionable(TRUE)
      ->setDefaultValueCallback(static::class . '::getRequestTime')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'timestamp',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 13,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time the asset was last edited.'))
      ->setRevisionable(TRUE);

    $fields['archived'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Archived'))
      ->setDescription(t('Whether the asset is archived.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(FALSE)
      ->setSetting('on_label', 'Yes')
      ->setSetting('off_label', 'No')
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'boolean',
        'settings' => [
          'format' => 'default',
          'format_custom_false' => '',
          'format_custom_true' => '',
        ],
        'weight' => 100,
      ])
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => 100,
      ]);

    return $fields;
  }

}

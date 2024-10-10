<?php

namespace Drupal\farm_export_csv\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Form\BaseFormIdInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\file\FileRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Provides an entity CSV action form.
 *
 * @see \Drupal\farm_export_csv\Plugin\Action\EntityCsv
 * @see \Drupal\Core\Entity\Form\DeleteMultipleForm
 */
class EntityCsvActionForm extends ConfirmFormBase implements BaseFormIdInterface {

  /**
   * The tempstore factory.
   *
   * @var \Drupal\Core\TempStore\SharedTempStore
   */
  protected $tempStore;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The serializer service.
   *
   * @var \Symfony\Component\Serializer\SerializerInterface
   */
  protected $serializer;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The default file scheme.
   *
   * @var string
   */
  protected $defaultFileScheme;

  /**
   * The file repository service.
   *
   * @var \Drupal\file\FileRepositoryInterface
   */
  protected $fileRepository;

  /**
   * The file URL generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * The entity type.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $entityType;

  /**
   * The entities to export.
   *
   * @var \Drupal\Core\Entity\EntityInterface[]
   */
  protected $entities;

  /**
   * Constructs an EntityCsvActionForm form object.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Symfony\Component\Serializer\SerializerInterface $serializer
   *   The serializer service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\file\FileRepositoryInterface $file_repository
   *   The file repository service.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $file_url_generator
   *   The file URL generator.
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The current user.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, SerializerInterface $serializer, FileSystemInterface $file_system, ConfigFactoryInterface $config_factory, FileRepositoryInterface $file_repository, FileUrlGeneratorInterface $file_url_generator, AccountInterface $user) {
    $this->tempStore = $temp_store_factory->get('entity_csv_confirm');
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->serializer = $serializer;
    $this->fileSystem = $file_system;
    $this->defaultFileScheme = $config_factory->get('system.file')->get('default_scheme') ?? 'public';
    $this->fileRepository = $file_repository;
    $this->fileUrlGenerator = $file_url_generator;
    $this->user = $user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('serializer'),
      $container->get('file_system'),
      $container->get('config.factory'),
      $container->get('file.repository'),
      $container->get('file_url_generator'),
      $container->get('current_user'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseFormId() {
    return 'entity_export_csv_action_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    // Get entity type ID from the route because ::buildForm has not yet been
    // called.
    $entity_type_id = $this->getRouteMatch()->getParameter('entity_type_id');
    return $entity_type_id . '_export_csv_action_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->formatPlural(count($this->entities), 'Export a CSV of @count @item?', 'Export a CSV of @count @items?', [
      '@item' => $this->entityType->getSingularLabel(),
      '@items' => $this->entityType->getPluralLabel(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    if ($this->entityType->hasLinkTemplate('collection')) {
      return new Url('entity.' . $this->entityType->id() . '.collection');
    }
    else {
      return new Url('<front>');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Export');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $entity_type_id = NULL) {

    // If we don't have an entity type or list of entities, redirect.
    $this->entityType = $this->entityTypeManager->getDefinition($entity_type_id);
    $this->entities = $this->tempStore->get($this->user->id() . ':' . $entity_type_id);
    if (empty($entity_type_id) || empty($this->entities)) {
      return new RedirectResponse($this->getCancelUrl()
        ->setAbsolute()
        ->toString());
    }

    // Make it clear that CSV exports are limited.
    $message = $this->t('Note: CSV exports do not include all @item data.', ['@item' => $this->entityType->getSingularLabel()]);
    $form['warning'] = [
      '#type' => 'html_tag',
      '#tag' => 'strong',
      '#value' => $message,
    ];

    // Determine which entity bundle(s) are represented.
    $bundles = [];
    foreach ($this->entities as $entity) {
      if (!in_array($entity->bundle(), $bundles)) {
        $bundles[] = $entity->bundle();
      }
    }

    // If multiple bundles are included, mention that only shared (base field)
    // columns will be included.
    if (count($bundles) > 1) {
      $message = $this->t('Exports that include multiple types of records will only include columns that are shared across all types. To include type-specific columns, limit the export to records of one type.');
      $form['bundles_warning'] = [
        '#type' => 'html_tag',
        '#tag' => 'strong',
        '#value' => $message,
      ];
    }

    // Allow columns to be selected for inclusion.
    // If all records are the same bundle, then include bundle fields.
    $bundle = count($bundles) === 1 ? reset($bundles) : NULL;
    $column_options = $this->getIncludeColumns($bundle);
    $form['columns'] = [
      '#type' => 'details',
      '#title' => $this->t('Columns'),
      '#tree' => TRUE,
    ];
    $form['columns']['include'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Include columns'),
      '#description' => $this->t('Which columns should be included in the CSV?'),
      '#options' => array_combine($column_options, $column_options),
      '#default_value' => $column_options,
      '#required' => TRUE,
    ];

    // Add a section for advanced options.
    $form['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced'),
    ];

    // Export unprocessed text.
    $form['advanced']['processed_text'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Export processed text'),
      '#description' => $this->t('Some long text fields can be passed through processing filters to remove disallowed HTML tags, convert line breaks into HTML, etc. This processing is disabled by default in CSV exports so that the raw user input is exported. Enable processing if you plan to embed CSV data directly in HTML documents.'),
      '#default_value' => FALSE,
    ];

    // Sanitize against CSV formula injection.
    $form['advanced']['sanitize'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Sanitize against formula injection'),
      '#description' => $this->t('Prepend cells that start with @, =, +, or - characters with a tab to prevent them from being interpreted as a formula. <strong>Warning: Opening unsanitized CSV files with spreadsheet applications may expose you to <a href=":link">formula injection</a> or other security vulnerabilities.</strong>', [':link' => 'https://owasp.org/www-community/attacks/CSV_Injection']),
      '#default_value' => TRUE,
    ];

    // Delegate to the parent method.
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Filter out entities the user doesn't have access to.
    $inaccessible_entities = [];
    $accessible_entities = [];
    foreach ($this->entities as $entity) {
      if (!$entity->access('view', $this->currentUser())) {
        $inaccessible_entities[] = $entity;
        continue;
      }
      $accessible_entities[] = $entity;
    }

    // Serialize the entities with the csv format.
    $context = [

      // Define the columns to include.
      'include_columns' => $form_state->getValue(['columns', 'include']),

      // Return processed text, if desired. Otherwise, raw user input will be
      // exported.
      'processed_text' => $form_state->getValue('processed_text'),

      // Return content entity labels and config entity IDs.
      'content_entity_labels' => TRUE,
      'config_entity_ids' => TRUE,

      // Return RFC3339 dates.
      'rfc3339_dates' => TRUE,

      // Return WKT geometry.
      'wkt' => TRUE,

      // CSV encoder settings.
      'csv_settings' => [
        'sanitize' => $form_state->getValue('sanitize'),
        'strip_tags' => FALSE,
      ],
    ];
    $output = $this->serializer->serialize($accessible_entities, 'csv', $context);

    // Prepare the file directory.
    $directory = $this->defaultFileScheme . '://csv';
    $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);

    // Create the file.
    $filename = 'csv_export-' . date('c') . '.csv';
    $destination = "$directory/$filename";
    try {
      $file = $this->fileRepository->writeData($output, $destination);
    }

    // If file creation failed, bail with a warning.
    catch (\Exception $e) {
      $this->messenger()->addWarning($this->t('Could not create file.'));
      return;
    }

    // Make the file temporary.
    $file->status = 0;
    $file->save();

    // Add warning message for inaccessible entities.
    if (!empty($inaccessible_entities)) {
      $inaccessible_count = count($inaccessible_entities);
      $this->messenger()->addWarning($this->formatPlural($inaccessible_count, 'Could not export @count @item because you do not have the necessary permissions.', 'Could not export @count @items because you do not have the necessary permissions.', [
        '@item' => $this->entityType->getSingularLabel(),
        '@items' => $this->entityType->getPluralLabel(),
      ]));
    }

    // Add confirmation message.
    if (count($accessible_entities)) {
      $this->messenger()->addStatus($this->formatPlural(count($accessible_entities), 'Exported @count @item.', 'Exported @count @items', [
        '@item' => $this->entityType->getSingularLabel(),
        '@items' => $this->entityType->getPluralLabel(),
      ]));
    }

    // Show a link to the file.
    $url = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
    $this->messenger()->addMessage($this->t('CSV file created: <a href=":url">%filename</a>', [
      ':url' => $url,
      '%filename' => $file->label(),
    ]));

    $this->tempStore->delete($this->currentUser()->id() . ':' . $this->entityType->id());
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

  /**
   * Get a list of columns to include in CSV exports.
   *
   * @param string|null $bundle
   *   If specified, columns that are specific to this bundle will be included.
   *
   * @return string[]
   *   An array of column names.
   */
  protected function getIncludeColumns(?string $bundle = NULL) {

    // Start with ID and UUID.
    $columns = [
      'id',
      'uuid',
    ];

    // Define which field types are supported.
    $supported_field_types = [
      'boolean',
      'created',
      'changed',
      'entity_reference',
      'fraction',
      'geofield',
      'list_string',
      'state',
      'string',
      'text_long',
      'timestamp',
    ];

    // Add base field for supported field types.
    $base_field_definitions = $this->entityFieldManager->getBaseFieldDefinitions($this->entityType->id());
    foreach ($base_field_definitions as $field_name => $field_definition) {
      if (!in_array($field_name, $columns) && in_array($field_definition->getType(), $supported_field_types)) {
        $columns[] = $field_name;
      }
    }

    // Add bundle fields for supported field types.
    if ($bundle) {
      if ($this->entityTypeManager->hasHandler($this->entityType->id(), 'bundle_plugin')) {
        $bundle_fields = $this->entityTypeManager->getHandler($this->entityType->id(), 'bundle_plugin')->getFieldDefinitions($bundle);
        foreach ($bundle_fields as $field_name => $field_definition) {
          if (!in_array($field_name, $columns) && in_array($field_definition->getType(), $supported_field_types)) {
            $columns[] = $field_name;
          }
        }
      }
    }

    // Remove revision and language columns.
    $remove_columns = [
      'default_langcode',
      'revision_translation_affected',
      'revision_created',
      'revision_user',
      'revision_default',
    ];
    $columns = array_filter($columns, function ($name) use ($remove_columns) {
      return !in_array($name, $remove_columns);
    });

    return $columns;
  }

}

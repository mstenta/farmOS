<?php

namespace Drupal\farm_ui_theme\Form;

use Drupal\Core\Entity\EntityConstraintViolationListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Taxonomy term form for gin content form.
 */
class TaxonomyTermForm extends GinContentFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getFieldGroups() {
    return parent::getFieldGroups() + [
      'reference' => [
        'location' => 'main',
        'title' => $this->t('Reference'),
        'weight' => 50,
      ],
      'relations' => [
        'location' => 'sidebar',
        'title' => $this->t('Relations'),
        'weight' => 50,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    // Term relations logic copied from Drupal\taxonomy\TermForm::form.
    $term = $this->entity;
    $vocab_storage = $this->entityTypeManager->getStorage('taxonomy_vocabulary');
    /** @var \Drupal\taxonomy\TermStorageInterface $taxonomy_storage */
    $taxonomy_storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $vocabulary = $vocab_storage->load($term->bundle());

    $parent = $this->getParentIds($term);
    $form_state->set(['taxonomy', 'parent'], $parent);
    $form_state->set(['taxonomy', 'vocabulary'], $vocabulary);

    // \Drupal\taxonomy\TermStorageInterface::loadTree() and
    // \Drupal\taxonomy\TermStorageInterface::loadParents() may contain large
    // numbers of items so we check for taxonomy.settings:override_selector
    // before loading the full vocabulary. Contrib modules can then intercept
    // before hook_form_alter to provide scalable alternatives.
    if (!$this->config('taxonomy.settings')->get('override_selector')) {
      $exclude = [];
      if (!$term->isNew()) {
        $children = $taxonomy_storage->loadTree($vocabulary->id(), $term->id());

        // A term can't be the child of itself, nor of its children.
        foreach ($children as $child) {
          $exclude[] = $child->tid;
        }
        $exclude[] = $term->id();
      }

      $tree = $taxonomy_storage->loadTree($vocabulary->id());
      $options = ['<' . $this->t('root') . '>'];
      if (empty($parent)) {
        $parent = [0];
      }

      foreach ($tree as $item) {
        if (!in_array($item->tid, $exclude)) {
          $options[$item->tid] = str_repeat('-', $item->depth) . $item->name;
        }
      }
    }
    else {
      $options = ['<' . $this->t('root') . '>'];
      $parent = [0];
    }

    if ($this->getRequest()->query->has('parent')) {
      $parent = array_values(array_intersect(
        array_keys($options),
        (array) $this->getRequest()->query->all()['parent'],
      ));
    }

    // The select field doesn't support #group so needs to be under the
    // relations_field_group form structure.
    $form['relations_field_group']['parent'] = [
      '#type' => 'select',
      '#title' => $this->t('Parent terms'),
      '#options' => $options,
      '#default_value' => $parent,
      '#multiple' => TRUE,
      '#group' => 'relations_field_group',
    ];

    // Textfields support #group so use that.
    $form['weight'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Weight'),
      '#size' => 6,
      '#default_value' => $term->getWeight(),
      '#description' => $this->t('Terms are displayed in ascending order by weight.'),
      '#required' => TRUE,
      '#group' => 'relations_field_group',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, FormStateInterface $form_state) {
    $term = parent::buildEntity($form, $form_state);

    // Prevent leading and trailing spaces in term names.
    $term->setName(trim($term->getName()));

    // Assign parents with proper delta values starting from 0.
    $term->parent = array_values($form_state->getValue('parent'));

    return $term;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditedFieldNames(FormStateInterface $form_state) {
    return array_merge(['parent', 'weight'], parent::getEditedFieldNames($form_state));
  }

  /**
   * {@inheritdoc}
   */
  protected function flagViolations(EntityConstraintViolationListInterface $violations, array $form, FormStateInterface $form_state) {
    // Manually flag violations of fields not handled by the form display. This
    // is necessary as entity form displays only flag violations for fields
    // contained in the display.
    // @see ::form()
    foreach ($violations->getByField('parent') as $violation) {
      $form_state->setErrorByName('parent', $violation->getMessage());
    }
    foreach ($violations->getByField('weight') as $violation) {
      $form_state->setErrorByName('weight', $violation->getMessage());
    }

    parent::flagViolations($violations, $form, $form_state);
  }

  /**
   * Returns term parent IDs, including the root.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   The taxonomy term entity.
   *
   * @return array
   *   A list if parent term IDs.
   */
  protected function getParentIds(TermInterface $term): array {
    $parent = [];
    // Get the parent directly from the term as
    // \Drupal\taxonomy\TermStorageInterface::loadParents() excludes the root.
    foreach ($term->get('parent') as $item) {
      $parent[] = (int) $item->target_id;
    }
    return $parent;
  }

}

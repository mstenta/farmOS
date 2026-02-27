<?php

declare(strict_types=1);

namespace Drupal\farm_report\Controller;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Report controller.
 */
class ReportController extends ControllerBase {

  use AutowireTrait;
  use StringTranslationTrait;

  public function __construct(
    protected MenuLinkTreeInterface $menuLinkTree,
  ) {}

  /**
   * The index of reports.
   *
   * @return array
   *   Returns a render array.
   */
  public function index(): array {

    // Load all menu links below it.
    $parameters = new MenuTreeParameters();
    $parameters->setRoot('farm.report')->excludeRoot()->setTopLevelOnly()->onlyEnabledLinks();
    $tree = $this->menuLinkTree->load('', $parameters);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $this->menuLinkTree->transform($tree, $manipulators);

    // Start cacheability for report list.
    $tree_access_cacheability = new CacheableMetadata();

    // Build list item for each report.
    $items = [];
    foreach ($tree as $element) {
      $tree_access_cacheability->addCacheableDependency($element->access);
      if ($element->access->isAllowed()) {
        $items[] = [
          'title' => $element->link->getTitle(),
          'description' => $element->link->getDescription(),
          'url' => $element->link->getUrlObject(),
        ];
      }
    }

    // Render items.
    if (!empty($items)) {
      $output = [
        '#theme' => 'admin_block_content',
        '#content' => $items,
      ];
    }
    else {
      $output = [
        // @todo Don't use "you".
        '#markup' => $this->t('You do not have any reports.'),
      ];
    }
    $tree_access_cacheability->applyTo($output);
    return $output;
  }

}

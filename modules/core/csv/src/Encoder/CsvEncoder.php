<?php

namespace Drupal\farm_csv\Encoder;

use Drupal\csv_serialization\Encoder\CsvEncoder as ContribCsvEncoder;

/**
 * Extend the contrib CsvEncoder class with custom header extraction logic.
 */
class CsvEncoder extends ContribCsvEncoder {

  /**
   * {@inheritdoc}
   */
  protected function extractHeaders(array $data, array $context = []) {
    $headers = [];

    // Iterate over each row and merge its headers.
    // This differs from the parent method in that it combines headers from all
    // rows instead of only using headers from the first row.
    foreach ($data as $row) {
      $headers = array_merge($headers, array_diff(array_keys($row), $headers));
    }

    // Use labels provided by the Views style plugin, if available.
    // This mimics the logic of the parent method for compatibility in Views
    // contexts.
    if (!empty($context['views_style_plugin'])) {
      $fields = $context['views_style_plugin']->view->field;
      $headers = array_map(function ($header) use ($fields) {
        return !empty($fields[$header]->options['label']) ? $fields[$header]->options['label'] : $header;
      }, $headers);
    }

    return $headers;
  }

}

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

    // Iterate over each row and accumulate the unique headers.
    // This differs from the parent method in that it combines headers from all
    // rows instead of only using headers from the first row.
    // This relies on the property of PHP arrays being ordered maps the header
    // keys will appear in the order they are first encountered when iterating
    // over all rows.
    $unique_headers = [];
    foreach ($data as $row) {
      foreach (array_keys($row) as $header) {
        if (!array_key_exists($header, $unique_headers)) {
          // The value assigned doesn't matter here.
          $unique_headers[$header] = 1;
        }
      }
    }
    $headers = array_keys($unique_headers);

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

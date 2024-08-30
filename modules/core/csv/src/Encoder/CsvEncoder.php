<?php

namespace Drupal\farm_csv\Encoder;

use Drupal\csv_serialization\Encoder\CsvEncoder as ContribCsvEncoder;

/**
 * Adds CSV encoder support for the Serialization API.
 */
class CsvEncoder extends ContribCsvEncoder {

  /**
   * Whether to sanitize cell values.
   *
   * @var bool
   */
  protected $sanitize = TRUE;

  /**
   * Constructs the class.
   *
   * @param string $delimiter
   *   Indicates the character used to delimit fields. Defaults to ",".
   * @param string $enclosure
   *   Indicates the character used for field enclosure. Defaults to '"'.
   * @param string $escape_char
   *   Indicates the character used for escaping. Defaults to "\".
   * @param bool $strip_tags
   *   Whether to strip tags from values or not. Defaults to TRUE.
   * @param bool $trim_values
   *   Whether to trim values or not. Defaults to TRUE.
   * @param bool $sanitize
   *   Whether to sanitize values against formula injection. Defaults to TRUE.
   */
  public function __construct($delimiter = ",", $enclosure = '"', $escape_char = "\\", $strip_tags = TRUE, $trim_values = TRUE, $sanitize = TRUE) {
    parent::__construct($delimiter, $enclosure, $escape_char, $strip_tags, $trim_values);
    $this->sanitize = $sanitize;
  }

  /**
   * {@inheritdoc}
   */
  protected function formatValue($value) {
    $value = parent::formatValue($value);

    // Sanitize against CSV injection vectors by prefixing cells that start with
    // suspicious characters (=, -, +, or @) with a tab.
    // @see https://georgemauer.net/2017/10/07/csv-injection.html
    if ($this->sanitize) {
      if (preg_match('/^[=@\-+]/', $value)) {
        $value = "\t" . $value;
      }
    }

    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function setSettings(array $settings) {
    parent::setSettings($settings);
    $this->sanitize = $settings['sanitize'] ?? $this->sanitize;
  }

}

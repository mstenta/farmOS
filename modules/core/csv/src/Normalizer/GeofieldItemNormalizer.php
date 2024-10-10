<?php

namespace Drupal\farm_csv\Normalizer;

use Drupal\geofield\Plugin\Field\FieldType\GeofieldItem;
use Drupal\serialization\Normalizer\FieldItemNormalizer;

/**
 * Normalizes Geofields for farmOS CSV exports.
 */
class GeofieldItemNormalizer extends FieldItemNormalizer {

  /**
   * The supported format.
   */
  const FORMAT = 'csv';

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []): array|string|int|float|bool|\ArrayObject|NULL {
    $data = parent::normalize($object, $format, $context);

    // Return the WKT value, if desired.
    if (isset($context['wkt']) && $context['wkt'] === TRUE) {
      return $data['value'];
    }

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, ?string $format = NULL, array $context = []): bool {
    return $data instanceof GeofieldItem && $format == static::FORMAT;
  }

}

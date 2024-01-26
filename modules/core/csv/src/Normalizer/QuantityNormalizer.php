<?php

namespace Drupal\farm_csv\Normalizer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\quantity\Entity\QuantityInterface;
use Drupal\serialization\Normalizer\ContentEntityNormalizer as CoreContentEntityNormalizer;

/**
 * Normalizes farmOS quantity entities for CSV exports.
 */
class QuantityNormalizer extends ContentEntityNormalizer {

  /**
   * The supported format.
   */
  const FORMAT = 'csv';

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = []): array|string|int|float|bool|\ArrayObject|NULL {
    $data = parent::normalize($entity, $format, $context);

    // Add log data, if desired.
    if (!empty($context['include_quantity_log_data'])) {

      // Look up the log that this quantity is attached to.

      // Add some data from the log.

    }

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, string $format = NULL, array $context = []): bool {
    return $data instanceof QuantityInterface && $format == static::FORMAT;
  }

}

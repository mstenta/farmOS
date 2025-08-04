<?php

namespace Drupal\farm_api\Normalizer;

use Drupal\jsonapi\JsonApiResource\Relationship;
use Drupal\jsonapi\JsonApiResource\RelationshipData;
use Drupal\jsonapi\JsonApiResource\ResourceIdentifier;
use Drupal\jsonapi\Normalizer\RelationshipNormalizer as CoreRelationshipNormalizer;

/**
 * Normalizes a JSON:API relationship object.
 *
 * @internal
 */
class RelationshipNormalizer extends CoreRelationshipNormalizer {

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []): array|string|int|float|bool|\ArrayObject|NULL {

    // This extends the core RelationshipNormalizer::normalize() method in order
    // to rebuild the resource identifier objects in the relationship data
    // object that is being normalized, to change "drupal_internal__target_id"
    // to "internal__target_id". Drupal core's JSON:API classes do not provide
    // methods for modifying the existing objects, so we need to rebuild them.
    /** @var \Drupal\jsonapi\JsonApiResource\RelationshipData $data */
    $data = $object->getData();
    /** @var \Drupal\jsonapi\JsonApiResource\ResourceIdentifier $resourceIdentifier */
    $resource_identifiers = array_map(function ($resourceIdentifier) {
      $meta = $resourceIdentifier->getMeta();
      if (!empty($meta['drupal_internal__target_id'])) {
        $meta['internal__target_id'] = $meta['drupal_internal__target_id'];
        unset($meta['drupal_internal__target_id']);
      }
      return new ResourceIdentifier($resourceIdentifier->getResourceType(), $resourceIdentifier->getId(), $meta);
    }, $object->getData()->toArray());
    $data = new RelationshipData($resource_identifiers, $data->getCardinality());

    // @todo this is where we hit a blocker... $object needs to be a Relationship
    // object, which cannot be created outside of Relationship::createFromEntityReferenceField()
    $object = new Relationship();

    // Delegate to the parent method.
    return parent::normalize($object, $format, $context);
  }

}

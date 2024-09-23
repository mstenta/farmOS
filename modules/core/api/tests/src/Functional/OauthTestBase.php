<?php

namespace Drupal\Tests\farm_api\Functional;

use Drupal\Tests\simple_oauth\Functional\TokenBearerFunctionalTestBase;

/**
 * Base class that handles common logic for OAuth tests.
 *
 * @group farm
 */
class OauthTestBase extends TokenBearerFunctionalTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = 'farm';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'image',
    'node',
    'serialization',
    'simple_oauth',
    'text',
    'user',
    'farm_api',
    'farm_api_default_consumer',
    'farm_api_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {

    parent::setUp();

    // Add a client_id to the client.
    $this->client->set('client_id', 'farm_test');
    $this->client->set('confidential', FALSE);

    // Add support for password grant and password scope consumer.
    $this->client->get('grant_types')->appendItem('password');
    $this->client->set('scopes', ['test:password']);

    // Save the client.
    $this->client->save();

    // Set the scope.
    $this->scope = 'test:password';
  }

}

<?php

namespace Druapl\tests\farm_role\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Url;
use Drupal\Tests\farm_api\Functional\OauthTestBase;

/**
 * Managed role permissions OAuth tests.
 *
 * @group farm
 */
class OauthPermissionsTest extends OauthTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'farm_role',
  ];

  /**
   * Test that managed role permissions are added during OAuth requests.
   */
  public function testOauthPermissions() {
    $valid_payload = [
      'grant_type' => 'password',
      'client_id' => $this->client->get('client_id')->value,
      'client_secret' => $this->clientSecret,
      'username' => $this->user->getAccountName(),
      'password' => $this->user->pass_raw,
      'scope' => $this->scope,
    ];

    // 1. Test a valid token response.
    $response = $this->post($this->url, $valid_payload);
    $this->assertValidTokenResponse($response, TRUE);

    // 2. Test that managed role permissions are added.
    $url = Url::fromRoute('jsonapi.log--test.collection');
    $response = $this->get($url);
    $this->assertEquals(200, $response->getStatusCode());
    // @todo
  }

}

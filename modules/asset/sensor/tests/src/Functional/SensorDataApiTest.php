<?php

namespace Drupal\Tests\farm_sensor\Functional;

use Drupal\asset\Entity\Asset;
use Drupal\asset\Entity\AssetInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Url;
use Drupal\data_stream\Entity\DataStream;
use Drupal\Tests\farm_test\Functional\FarmBrowserTestBase;
use GuzzleHttp\RequestOptions;

/**
 * Test the Sensor data API.
 *
 * @group farm
 */
class SensorDataApiTest extends FarmBrowserTestBase {

  /**
   * The Sensor asset for testing.
   *
   * @var \Drupal\asset\Entity\AssetInterface
   */
  protected $asset;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'farm_sensor',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->asset = Asset::create([
      'type' => 'sensor',
      'name' => $this->randomMachineName(),
    ]);
    $this->asset->save();
  }

  /**
   * Test that references to data streams are removed when deleted.
   */
  public function testRemoveDataStreamReference() {

    // @todo Run this test once issue 485 is fixed.
    $this->markTestSkipped('Skip this test until integration with entity reference integrity is fixed. Issue 485.');

    // Create a data stream.
    $data_stream = DataStream::create([
      'type' => 'basic',
      'name' => $this->randomMachineName(),
    ]);
    $data_stream->save();

    // Add to the asset.
    $this->asset->set('data_stream', $data_stream);
    $this->asset->save();

    // Save the data stream ID.
    $data_stream_id = $data_stream->id();

    // First assert that the data stream is referenced.
    $count = \Drupal::database()->select('asset__data_stream')
      ->condition('data_stream_target_id', $data_stream_id)
      ->countQuery()
      ->execute()
      ->fetchfield();
    $this->assertGreaterThan(0, $count);

    // Delete the data stream.
    $data_stream->delete();

    // Assert that the data stream is no longer referenced.
    $count = \Drupal::database()->select('asset__data_stream')
      ->condition('data_stream_target_id', $data_stream_id)
      ->countQuery()
      ->execute()
      ->fetchField();
    $this->assertEquals(0, $count);
  }

  /**
   * Test API GET requests.
   */
  public function testApiGet() {

    // Build the path.
    $uri = $this->buildPath($this->asset);
    $url = Url::fromUri($uri);

    // Build a private_key query param.
    $private_key = [RequestOptions::QUERY => ['private_key' => $this->asset->get('private_key')->value]];

    // Make a request.
    $response = $this->processRequest('GET', $url);

    // Assert that access is denied.
    $this->assertEquals(403, $response->getStatusCode());

    // Make a request with the private key.
    $response = $this->processRequest('GET', $url, $private_key);

    // Assert valid response.
    $this->assertEquals(200, $response->getStatusCode());
    $data = Json::decode($response->getBody());
    $this->assertEquals(0, count($data));

    // Make the sensor public.
    $this->asset->set('public', TRUE)->save();

    // Test that data can be accessed without the private key.
    $response = $this->processRequest('GET', $url);
    $this->assertEquals(200, $response->getStatusCode());
    $data = Json::decode($response->getBody());
    $this->assertEquals(0, count($data));
  }

  /**
   * Test API POST requests.
   */
  public function testApiPost() {

    // Build the path.
    $uri = $this->buildPath($this->asset);
    $url = Url::fromUri($uri);

    // Build a private_key query param.
    $private_key = [RequestOptions::QUERY => ['private_key' => $this->asset->get('private_key')->value]];

    // Make the asset public. This should not matter for posting data.
    $this->asset->set('public', TRUE)->save();

    // Test data.
    $test_data = ['test_1' => 100, 'test_2' => 200];

    // Make a request without a private key.
    $payload = [RequestOptions::BODY => Json::encode($test_data)];
    $response = $this->processRequest('POST', $url, $payload);

    // Assert that access is denied.
    $this->assertEquals(403, $response->getStatusCode());

    // Post data with a private key.
    $response = $this->processRequest('POST', $url, $private_key + $payload);
    $this->assertEquals(201, $response->getStatusCode());

    // Assert that new data streams were created.
    $this->asset = Asset::load($this->asset->id());
    $data_streams = $this->asset->get('data_stream')->referencedEntities();
    $this->assertEquals(2, count($data_streams));

    // Assert that new data was saved in DB.
    $response = $this->processRequest('GET', $url);
    $data = Json::decode($response->getBody());
    $this->assertEquals(2, count($data));

    // More test data.
    $test_data = ['test_1' => 101, 'test_2' => 201];

    // Post data with a private key.
    $payload = [RequestOptions::BODY => Json::encode($test_data)];
    $response = $this->processRequest('POST', $url, $private_key + $payload);
    $this->assertEquals(201, $response->getStatusCode());

    // Assert that no new data streams were created.
    $this->asset = Asset::load($this->asset->id());
    $data_streams = $this->asset->get('data_stream')->referencedEntities();
    $this->assertEquals(2, count($data_streams));

    // Assert that new data was saved in DB.
    $response = $this->processRequest('GET', $url);
    $data = Json::decode($response->getBody());
    $this->assertEquals(4, count($data));
  }

  /**
   * Helper function to build the path to the sensor API.
   *
   * @param \Drupal\asset\Entity\AssetInterface $asset
   *   The asset.
   *
   * @return string
   *   The path.
   */
  protected function buildPath(AssetInterface $asset) {
    return "base://asset/{$asset->uuid()}/data/basic";
  }

  /**
   * Process a request.
   *
   * @param string $method
   *   HTTP method.
   * @param \Drupal\Core\Url $url
   *   URL to request.
   * @param array $request_options
   *   Request options to apply.
   *
   * @return \GuzzleHttp\Psr7\Response
   *   The response.
   *
   * @see \Drupal\Tests\jsonapi\Functional\JsonApiRequestTestTrait
   */
  protected function processRequest(string $method, Url $url, array $request_options = []) {
    $this->refreshVariables();
    $request_options[RequestOptions::HTTP_ERRORS] = FALSE;
    $client = $this->getSession()->getDriver()->getClient()->getClient();
    return $client->request($method, $url->setAbsolute(TRUE)->toString(), $request_options);
  }

}

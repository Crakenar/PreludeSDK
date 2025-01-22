<?php

namespace Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Mockery;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Tests\TestCase;
use Wigl\WiglSmsPackage\dto\CreateVerificationOptions;
use Wigl\WiglSmsPackage\service\SmsService;

class SmsServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Mock the config facade
        Mockery::mock('alias:config')
            ->shouldReceive('get')
            ->with('services.sms_service.api_key', null)
            ->andReturn('test-api-key');

        Mockery::mock('alias:config')
            ->shouldReceive('get')
            ->with('services.sms_service.service_activated', null)
            ->andReturn(true);

    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @throws GuzzleException
     */
    public function testCreateVerificationRequest()
    {
        // Mock the Guzzle client
        $mockedClient = Mockery::mock(Client::class);
        $mockedResponse = Mockery::mock(ResponseInterface::class);
        $mockedStream = Mockery::mock(StreamInterface::class);

        // Set up the mocked stream to return a JSON string
        $mockedStream->shouldReceive('getContents')->once()->andReturn('{"id": 1}');

        $mockedResponse->shouldReceive('getBody')->once()->andReturn($mockedStream);
        $mockedClient->shouldReceive('post')->once()->andReturn($mockedResponse);

        // Create the SmsService instance
        $smsService = new SmsService();
        $smsService->setClient($mockedClient);

        // Set up the options for the request
        $options = new CreateVerificationOptions();
        $options->setOptions('en-US', '1234');
        $options->setSignals('device123', 'android', '192.168.1.1', '1.0.0');

        // Call the method to test
        $response = $smsService->createVerificationRequest('+1234567890', $options);
        $responseContent = json_decode($response->getBody()->getContents(), true);

        // Assertions
        $this->assertIsObject($response);
        $this->assertArrayHasKey('id', $responseContent);
        $this->assertEquals(1, $responseContent['id']);
    }
}

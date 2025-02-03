<?php

namespace Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use Illuminate\Support\Facades\Log;
use Mockery;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Tests\TestCase;
use Prelude\SmsSDK\DTO\CreateVerificationOptions;
use Prelude\SmsSDK\Services\SmsService;

class SmsServiceTest extends TestCase
{
    protected $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = require("config/constants.php");

        // Mock the config facade
        Mockery::mock('alias:config')
            ->shouldReceive('get')
            ->with('services.sms_service.api_key', null)
            ->andReturn('test-api-key');

        Mockery::mock('alias:config')
            ->shouldReceive('get')
            ->with('services.sms_service.service_activated', null)
            ->andReturn(true);

        $this->smsService = Mockery::mock(SmsService::class)
            ->makePartial()
            ->setErrorCodes($this->config['sms_error_codes']);
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

    /**
     * Test that the handleException method returns the correct SmsPackageResponse when an exception is thrown.
     */
    public function testHandleExceptionWithException()
    {
        // Prepare the JSON body that mimics a real Guzzle exception response
        $responseBody = json_encode([
            'code' => 'invalid_phone_number',
            'message' => 'The provided phone number is invalid. Provide a valid E.164 phone number.',
            'type' => 'bad_request',
            'request_id' => '3d19215e-2991-4a05-a41a-527314e6ff6a'
        ]);
        //Mock the Guzzle Exception
        $mockedStream = Mockery::mock(Stream::class);
        $mockedStream->shouldReceive('getContents')->once()->andReturn($responseBody);
        $mockedResponse = Mockery::mock(Response::class);
        $mockedResponse->shouldReceive('getBody')->once()->andReturn($mockedStream);
        $mockedResponse->shouldReceive('getStatusCode')->once()->andReturn(400); // Status code for bad request
        $exception = new ClientException('Client error', Mockery::mock('Psr\Http\Message\RequestInterface'), $mockedResponse);

        $this->smsService->shouldReceive('formatError')
            ->once()
            ->with('invalid_phone_number', 'The provided phone number is invalid. Provide a valid E.164 phone number.')
            ->andReturn([
                'error_code' => $this->config['sms_error_codes']['invalid_phone_number'],
                'message' => 'The provided phone number is invalid. Provide a valid E.164 phone number.'
            ]);

        // Call handleApiException with the mocked exception
        $result = $this->smsService->handleApiException('+1234567890', $exception);

        // Assertions
        $this->assertFalse($result->getSuccess());
        $this->assertEquals([
            'error_code' => $this->config['sms_error_codes']['invalid_phone_number'],
            'message' => 'The provided phone number is invalid. Provide a valid E.164 phone number.'
        ], $result->getErrors());
    }

    /**
     * Test that the handleException method returns the correct SmsPackageResponse when no exception is passed.
     */
    public function testHandleExceptionWithoutException()
    {
        // Mock the logger to capture logs
        Log::shouldReceive('channel')
            ->once()
            ->with('sms_mode')
            ->andReturnSelf();

        // Adjust the mock to match the dynamic error message
        Log::shouldReceive('error')
            ->once()
            ->with(Mockery::on(function($message) {
                return str_contains($message, 'Error with trying to send an sms to 1234567890');
            }));

        $response = $this->smsService->handleApiException('1234567890');

        // Assertions
        // Check that the response indicates failure
        $this->assertFalse($response->getSuccess());

        // Check that the error format is correctly returned (generic error)
        $this->assertEquals([
            'error_code' => $this->config['sms_error_codes']['generic'],
            'message' => 'Error with trying to send an sms to 1234567890'
        ], $response->getErrors());
    }
}

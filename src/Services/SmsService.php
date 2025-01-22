<?php

namespace Wigl\WiglSmsPackage\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use Wigl\WiglSmsPackage\Contracts\SmsServiceInterface;
use Wigl\WiglSmsPackage\DTO\CreateVerificationOptions;
use Wigl\WiglSmsPackage\DTO\SmsPackageResponse;

class SmsService implements SmsServiceInterface
{
    private string|null $apiKey;
    private bool $isServiceEnabled = false;
    private mixed $errorCodes;
    private Client $client;

    //todo use config php
    private const string URL_V2 = "https://api.prelude.dev/v2";

    public function __construct()
    {
        //Default SMS Service CONFIG
        $this->errorCodes = config('error-constants.sms_error_codes');
        $this->isServiceEnabled = config('services.sms_service.service_activated') || app()->env === 'testing';
        $this->apiKey = config('services.sms_service.api_key');
        $this->client = new Client();
    }

    // Setter for the client (for testing purposes)
    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    /**
     * Sends a verification SMS to a user's phone number.
     * GuzzleException: Expect a code, message, doc_url => https://docs.prelude.so/api-reference/v2/errors
     * @param string $userPhoneNumber The phone number to send the SMS.
     * @param CreateVerificationOptions $options
     * @return SmsPackageResponse
     */
    public function sendVerification(string $userPhoneNumber, CreateVerificationOptions $options): SmsPackageResponse
    {
//        if ($this->isServiceEnabled) {
//            return true;
//        }
        try {
            $responseContent = json_decode($this->createVerificationRequest($userPhoneNumber, $options)->getBody()->getContents());
            if ($responseContent->id) {
                Log::channel('sms_mode')->info('SMS successfully dispatched to ' . $userPhoneNumber);
                return new SmsPackageResponse();
            }
            Log::channel('sms_mode')->error('Error with Prelude API request. Returned : ' . $responseContent);
            $errorsFormatted = $this->formatError($this->errorCodes['generic'], 'Error with trying to send an sms to ' . $userPhoneNumber);
            return new SmsPackageResponse(false, $errorsFormatted);

        } catch (GuzzleException $exception) {
            $response = $exception->getResponse();
            $responseBody = json_decode($response->getBody()->getContents(), true);
            dd($responseBody);
            $errorCode = $responseBody['code'] ?? 'generic';

            Log::channel('sms_mode')->error('Error with Prelude API request: ' . $exception->getMessage());
            $errorsFormatted = $this->formatError($errorCode, $exception->getMessage());
            return new SmsPackageResponse(false, $errorsFormatted);
        }
    }

    /**
     * Creates a verification request to the Prelude API.
     *
     * @param string $phoneNumber The recipient's phone number.
     * @param CreateVerificationOptions $options
     * @return ResponseInterface|string
     * @throws GuzzleException
     */
    public function createVerificationRequest(string $phoneNumber, CreateVerificationOptions $options): ResponseInterface|string
    {
        return $this->client->post(self::URL_V2 . '/verification', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => array_merge(
                [
                    'target' => [
                        'type' => 'phone_number',
                        'value' => $phoneNumber,
                    ],
                ],
                $options->toArray() // Merge in options and signals
            ),
        ]);
    }

    /**
     * Formats an error response for Prelude API errors.
     *
     * @param string $errorCode The specific error code.
     * @param string|null $message Additional error message details.
     * @return array Formatted error data.
     */
    public function formatError(string $errorCode, ?string $message = 'Unknown error'): array
    {
        return [
            'error_code' => $this->errorCodes[$errorCode] ?? 'Unknown error code.',
            'message' => $message,
        ];
    }
}

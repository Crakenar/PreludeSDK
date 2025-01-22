<?php

namespace Wigl\WiglSmsPackage\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use Wigl\WiglSmsPackage\Contracts\SmsServiceInterface;
use Wigl\WiglSmsPackage\DTO\CreateVerificationOptions;
use Wigl\WiglSmsPackage\DTO\SmsPackageResponse;
use Wigl\WiglSmsPackage\Utility\PhoneNumberValidator;

class SmsService implements SmsServiceInterface
{
    private string|null $apiKey;
    private bool $isServiceEnabled = false;
    private mixed $errorCodes;
    private Client $client;

    private const string URL_V2 = "https://api.prelude.dev/v2";

    public function __construct(
        string|null $apiKey = null,
        bool $isServiceEnabled = null,
        mixed $errorCodes = null
    ) {
        $this->apiKey = $apiKey ?? config('services.sms_service.api_key');
        $this->isServiceEnabled = $isServiceEnabled ?? (config('services.sms_service.service_activated') || app()->env === 'testing');
        $this->errorCodes = $errorCodes ?? config('constants.sms_error_codes');
        $this->client = new Client();
    }

    public function setApiKey(string|null $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function setIsServiceEnabled(bool $isServiceEnabled): self
    {
        $this->isServiceEnabled = $isServiceEnabled;
        return $this;
    }

    public function setErrorCodes(mixed $errorCodes): self
    {
        $this->errorCodes = $errorCodes;
        return $this;
    }

    // Setter for the client (for testing purposes)
    public function setClient(Client $client): self
    {
        $this->client = $client;
        return $this;
    }

    /**
     * Sends a verification SMS to a user's phone number.
     * GuzzleException Body: Expect a code, message, doc_url => https://docs.prelude.so/api-reference/v2/errors
     * @param string $userPhoneNumber The phone number to send the SMS.
     * @param CreateVerificationOptions $options
     * @return SmsPackageResponse
     */
    public function sendVerification(string $userPhoneNumber, CreateVerificationOptions $options): SmsPackageResponse
    {
//        if ($this->isServiceEnabled) {
//            return true;
//        }
        if (!PhoneNumberValidator::isValidE164($userPhoneNumber)) {
            $errorsFormatted = $this->formatError($this->errorCodes['invalid_phone_number'], 'Invalid phone number');
            return new SmsPackageResponse(false, $errorsFormatted);
        }
        try {
            $responseContent = json_decode($this->createVerificationRequest($userPhoneNumber, $options)->getBody()->getContents());
            if ($responseContent->id) {
                Log::channel('sms_mode')->info('SMS successfully dispatched to ' . $userPhoneNumber);
                return new SmsPackageResponse();
            }

            return $this->handleApiException($userPhoneNumber);

        } catch (GuzzleException $exception) {
            return $this->handleApiException($userPhoneNumber, $exception);
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
     * @param string $errorCode The specific error code. invalid_phone_number, generic...
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

    /**
     * Handles exceptions by logging error details and returning a formatted SMS package response.
     * Either in case of an exception or when no id is returned from the api.
     *
     * @param GuzzleException|null $exception The exception to be handled. If no exception, a default error is used.
     * @param string $userPhoneNumber The phone number to be included in the error message when no exception is provided. Optional.
     * @return SmsPackageResponse Returns an instance of `SmsPackageResponse` with `success` set to false and the formatted error details.
     *
     */
    public function handleApiException(string $userPhoneNumber, ?GuzzleException $exception = null): SmsPackageResponse
    {
        $errorMessage = 'Error with Prelude API request.';
        $errorCode = 'generic';

        if (isset($exception)) {
            $response = $exception->getResponse();
            $responseBody = json_decode($response->getBody()->getContents(), true);
            $errorCode = $responseBody['code'] ?? $errorCode;
            $errorMessage = $responseBody['message'] ?? $errorMessage;
        } else {
            $errorMessage = 'Error with trying to send an sms to ' . $userPhoneNumber;
        }

        Log::channel('sms_mode')->error($errorMessage);
        $errorsFormatted = $this->formatError($errorCode, $errorMessage);
        return new SmsPackageResponse(false, $errorsFormatted);
    }
}

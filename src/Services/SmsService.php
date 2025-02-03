<?php

namespace Prelude\SmsSDK\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use Prelude\SmsSDK\Contracts\SmsServiceInterface;
use Prelude\SmsSDK\DTO\CreateVerificationOptions;
use Prelude\SmsSDK\DTO\SmsPackageResponse;
use Prelude\SmsSDK\Enums\VerificationCheckCodeRequestStatus;
use Prelude\SmsSDK\Enums\VerificationRequestStatus;
use Prelude\SmsSDK\Utility\PhoneNumberValidator;

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
            if ($responseContent->id && in_array($responseContent->status, [VerificationRequestStatus::SUCCESS->value, VerificationRequestStatus::RETRY->value])) {
                Log::channel('sms_mode')->info('SMS successfully dispatched to ' . $userPhoneNumber);
                return new SmsPackageResponse();
            }

            return $this->handleApiException($userPhoneNumber);

        } catch (GuzzleException $exception) {
            return $this->handleApiException($userPhoneNumber, $exception);
        }
    }

    /**
     * Send a verification check code request to the API to validate the provided phone number and code.
     *
     * This method checks the provided phone number and code for validity. If the phone number is not in
     * valid E.164 format or the code is empty, an error response is returned. It then attempts to send the
     * verification check code request to an external API. If the response is successful, a success response
     * is returned. Otherwise, an API error is handled and returned.
     *
     * @param string $userPhoneNumber The phone number to validate in E.164 format (e.g., "+1234567890").
     * @param string $code The verification code cannot be empty. The format + expiration time is  managed by the API.
     *
     * @return SmsPackageResponse The result of the SMS verification check request, which can either be
     *         a successful response or an error response with relevant error messages.
     *
     */
    public function sendVerificationCheckCode(string $userPhoneNumber, string $code): SmsPackageResponse
    {
//        if ($this->isServiceEnabled) {
//            return true;
//        }
        if (!PhoneNumberValidator::isValidE164($userPhoneNumber)) {
            $errorsFormatted = $this->formatError($this->errorCodes['invalid_phone_number'], 'Invalid phone number');
            return new SmsPackageResponse(false, $errorsFormatted);
        }
        if (empty($code)) {
            $errorsFormatted = $this->formatError($this->errorCodes['code_empty'], 'No code provided');
            return new SmsPackageResponse(false, $errorsFormatted);
        }
        try {
            $responseContent = json_decode($this->createVerificationCheckCodeRequest($userPhoneNumber, $code)->getBody()->getContents());
            if ($responseContent->id && $responseContent->status === VerificationCheckCodeRequestStatus::SUCCESS->value) {
                Log::channel('sms_mode')->info('SMS Code validated successfully for ' . $userPhoneNumber);
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
                $options->toArray()
            ),
        ]);
    }

    /**
     * Sends a request to verify the provided code for a phone number.
     *
     * @param string $phoneNumber The phone number in E.164 format to verify.
     * @param string $code The verification code to check against the provided phone number.
     * @return ResponseInterface|string The response from the external service.
     *         This could either be a response object or an error message string if the request fails.
     * @throws GuzzleException If there is an error during the HTTP request (e.g., connection failure, timeout, see constants.php).
     */
    public function createVerificationCheckCodeRequest(string $phoneNumber, string $code): ResponseInterface|string
    {
        return $this->client->post(self::URL_V2 . '/verification/check', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                    'target' => [
                        'type' => 'phone_number',
                        'value' => $phoneNumber,
                    ],
                    'code' => $code
                ],
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

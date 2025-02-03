<?php

namespace Prelude\SmsSDK\Contracts;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Prelude\SmsSDK\DTO\CreateVerificationOptions;
use Prelude\SmsSDK\DTO\SmsPackageResponse;

interface SmsServiceInterface
{

    /**
     * Set the API key for the SMS service.
     *
     * @param Client $client
     * @return self
     */
    public function setClient(Client $client): self;

    /**
     * Set the API key for the SMS service.
     *
     * @param string|null $apiKey
     * @return self
     */
    public function setApiKey(string|null $apiKey): self;

    /**
     * Set whether the SMS service is enabled.
     *
     * @param bool $isServiceEnabled
     * @return self
     */
    public function setIsServiceEnabled(bool $isServiceEnabled): self;

    /**
     * Set the error codes configuration.
     *
     * @param mixed $errorCodes
     * @return self
     */
    public function setErrorCodes(mixed $errorCodes): self;

    /**
     * Sends a verification SMS to a user's phone number.
     *
     * @param string $userPhoneNumber The phone number to send the SMS.
     * @param CreateVerificationOptions $options Optional configuration that helps prelude with client language and anti-fraud.
     * @return SmsPackageResponse
     */
    public function sendVerification(string $userPhoneNumber, CreateVerificationOptions $options): SmsPackageResponse;

    /**
     * Send a verification check code request to the API to validate the provided phone number and code.
     *
     * @param string $userPhoneNumber The phone number to send the SMS.
     * @param string $code
     * @return SmsPackageResponse
     */
    public function sendVerificationCheckCode(string $userPhoneNumber, string $code): SmsPackageResponse;

    /**
     * Formats an error response for Prelude API errors.
     *
     * @param string $errorCode The specific error code.
     * @param string|null $message Additional error message details.
     * @return array Formatted error data.
     */
    public function formatError(string $errorCode, ?string $message = null): array;

    /**
     * Handles exceptions by logging error details and returning a formatted SMS package response.
     * @param string $userPhoneNumber
     * @param GuzzleException|null $exception
     * @return SmsPackageResponse
     */
    public function handleApiException(string $userPhoneNumber, GuzzleException $exception = null): SmsPackageResponse;
}

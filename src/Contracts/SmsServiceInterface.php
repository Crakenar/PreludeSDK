<?php

namespace Wigl\WiglSmsPackage\Contracts;

use Wigl\WiglSmsPackage\DTO\CreateVerificationOptions;
use Wigl\WiglSmsPackage\DTO\SmsPackageResponse;

interface SmsServiceInterface
{
    /**
     * Sends a verification SMS to a user's phone number.
     *
     * @param string $userPhoneNumber The phone number to send the SMS.
     * @param CreateVerificationOptions $options Optional configuration that helps prelude with client language and anti-fraud.
     * @return SmsPackageResponse
     */
    public function sendVerification(string $userPhoneNumber, CreateVerificationOptions $options): SmsPackageResponse;

    /**
     * Formats an error response for Prelude API errors.
     *
     * @param string $errorCode The specific error code.
     * @param string|null $message Additional error message details.
     * @return array Formatted error data.
     */
    public function formatError(string $errorCode, ?string $message = null): array;
}

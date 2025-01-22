<?php

namespace Wigl\WiglSmsPackage\interface;

use Wigl\WiglSmsPackage\dto\CreateVerificationOptions;

interface SmsServiceInterface
{
    /**
     * Sends a verification SMS to a user's phone number.
     *
     * @param string $userPhoneNumber The phone number to send the SMS.
     * @param CreateVerificationOptions $options Optional configuration that helps prelude with client language and anti-fraud.
     * @return bool True if successful, false otherwise.
     */
    public function sendVerification(string $userPhoneNumber, CreateVerificationOptions $options): bool;

    /**
     * Formats an error response for Prelude API errors.
     *
     * @param string $errorCode The specific error code.
     * @param string|null $message Additional error message details.
     * @return array Formatted error data.
     */
    public function formatError(string $errorCode, ?string $message = null): array;
}

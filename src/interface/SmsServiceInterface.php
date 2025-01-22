<?php

namespace Wigl\WiglSmsPackage\interface;

interface SmsServiceInterface
{
    /**
     * Sends a verification SMS to a user's phone number.
     *
     * @param string $userPhoneNumber The phone number to send the SMS.
     * @return bool True if successful, false otherwise.
     */
    public function sendVerification(string $userPhoneNumber): bool;

    /**
     * Formats an error response for Prelude API errors.
     *
     * @param string $errorCode The specific error code.
     * @param string|null $message Additional error message details.
     * @return array Formatted error data.
     */
    public function formatError(string $errorCode, ?string $message = null): array;
}

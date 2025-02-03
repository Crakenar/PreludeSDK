<?php

namespace Prelude\SmsSDK\Contracts;

/**
 * Interface PackageResponseInterface
 *
 * Defines the structure of the response returned by an API service or package.
 * It includes methods to retrieve success status, error details, and specific error codes/messages.
 *
 * @package App\Contracts
 */
interface PackageResponseInterface
{
    /**
     * Get the success status of the response.
     *
     * @return bool Returns true if the operation was successful, false otherwise.
     */
    public function getSuccess(): bool;

    /**
     * Get a list of errors associated with the response.
     *
     * @return array<string, string> Returns an array of error details.
     *               Each error typically includes a code and message.
     */
    public function getErrors(): array;

    /**
     * Get the error code associated with the response.
     *
     * @return string Returns the error code.
     *                This can be used to identify the type of error (e.g., account_invalid, suspended_account).
     */
    public function getErrorCode(): string;

    /**
     * Get the error message associated with the response.
     *
     * @return string Returns a human-readable message explaining the error.
     *                This message can be displayed to users or logged for troubleshooting.
     */
    public function getErrorMessage(): string;
}

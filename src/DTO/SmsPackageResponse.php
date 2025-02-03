<?php
namespace Prelude\SmsSDK\DTO;

use Prelude\SmsSDK\Contracts\PackageResponseInterface;

/**
 * Class SmsPackageResponse
 *
 * Represents a response from the SMS package, implementing the ApiResponse interface.
 *
 * @package App\Services
 */
class SmsPackageResponse implements PackageResponseInterface
{
    /**
     * @var bool
     */
    protected bool $success;

    /**
     * @var array
     */
    protected array $errors;

    /**
     * SmsPackageResponse constructor.
     *
     * @param bool $success
     * @param array $errors
     */
    public function __construct(bool $success = true, array $errors = [])
    {
        $this->success = $success;
        $this->errors = $errors;
    }

    /**
     * Get the success status of the response.
     *
     * @return bool
     */
    public function getSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Get a list of errors associated with the response.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get the error code associated with the response.
     *
     * @return string
     */
    public function getErrorCode(): string
    {
        return $this->errors['error_code'] ?? 'generic';
    }

    /**
     * Get the error message associated with the response.
     *
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errors['message'] ?? 'no_error_message';
    }
}

<?php

namespace Prelude\SmsSDK\Enums;

enum VerificationCheckCodeRequestStatus: string
{
    case SUCCESS = 'success';
    case FAILURE = 'failure';
    case EXPIRED_OR_NOT_FOUND = 'expired_or_not_found';

    /**
     * Get a human-readable description of the status.
     */
    public function description(): string
    {
        return match($this) {
            self::SUCCESS => 'The verification check code was successful.',
            self::FAILURE => 'The verification check code failed.',
            self::EXPIRED_OR_NOT_FOUND => 'The verification check code is expired or not found.',
        };
    }
}

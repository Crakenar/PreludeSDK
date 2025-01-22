<?php

namespace Wigl\WiglSmsPackage\Enums;

enum VerificationRequestStatus: string
{
    case SUCCESS = 'success';
    case RETRY = 'retry';
    case BLOCKED = 'blocked ';

    /**
     * Get a human-readable description of the status.
     */
    public function description(): string
    {
        return match($this) {
            self::SUCCESS => 'The verification sms request was successful.',
            self::RETRY => 'The verification sms request was retried.',
            self::BLOCKED => 'The verification sms request was blocked.',
        };
    }
}

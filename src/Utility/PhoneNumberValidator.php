<?php

namespace Wigl\WiglSmsPackage\Utility;

class PhoneNumberValidator
{
    /**
     * Check if a phone number is valid E.164 format.
     *
     * @param string $phoneNumber
     * @return bool
     */
    public static function isValidE164(string $phoneNumber): bool
    {
        return preg_match('/^\+[1-9]\d{1,14}$/', $phoneNumber);
    }
}

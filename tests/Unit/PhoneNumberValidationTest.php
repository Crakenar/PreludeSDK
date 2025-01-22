<?php

use Tests\TestCase;
use Wigl\WiglSmsPackage\Utility\PhoneNumberValidator;

class PhoneNumberValidationTest extends TestCase
{
    public function testValidE164Number()
    {
        $validNumber = '+33757043460'; // Valid E.164 phone number
        $this->assertTrue(PhoneNumberValidator::isValidE164($validNumber));
    }

    public function testMissingPlusSign()
    {
        $invalidNumber = '33757043460'; // Missing "+" sign
        $this->assertFalse(PhoneNumberValidator::isValidE164($invalidNumber));
    }

    public function testInvalidCountryCode()
    {
        $invalidNumber = '+00123456789'; // Invalid country code
        $this->assertFalse(PhoneNumberValidator::isValidE164($invalidNumber));
    }

    public function testTooManyDigits()
    {
        $invalidNumber = '+1234567890123456'; // Too many digits (16)
        $this->assertFalse(PhoneNumberValidator::isValidE164($invalidNumber));
    }

    public function testEmptyString()
    {
        $invalidNumber = ''; // Empty string
        $this->assertFalse(PhoneNumberValidator::isValidE164($invalidNumber));
    }

    public function testNonNumericCharacters()
    {
        $invalidNumber = '+12345abc'; // Contains letters
        $this->assertFalse(PhoneNumberValidator::isValidE164($invalidNumber));
    }

    public function testSpacesInNumber()
    {
        $invalidNumber = '+123 456'; // Contains spaces
        $this->assertFalse(PhoneNumberValidator::isValidE164($invalidNumber));
    }

    public function testSpecialCharactersInNumber()
    {
        $invalidNumber = '+123-456'; // Contains dashes
        $this->assertFalse(PhoneNumberValidator::isValidE164($invalidNumber));
    }
}

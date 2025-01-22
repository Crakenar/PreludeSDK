# Wigl Sms Package

The `Wigl Sms Package` provides an easy-to-use service for sending SMS verifications through the Prelude API. This package allows developers to send SMS verification codes to users' phone numbers and manage options and signals for enhanced functionality.

## Table of Contents

- [Package Structure](#package-structure)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
    - [Creating Verification Request](#creating-verification-request)
    - [Using Options and Signals](#using-options-and-signals)
- [Customization](#customization)
- [Error Handling](#error-handling)
- [Testing](#testing)

## Package Structure
```bash
app/
├── Contracts/                  # Contains interfaces like PackageResponseInterface
├── Services/                   # Contains the logic for external services like SmsService
│   └── SmsPackageResponse.php  # Your implementation of the PackageResponseInterface interface
└── tests/                      # Contains the logic for testing the package
    └── Feature/            
    └── Unit/
        └── SmsServiceTest.php  # Default test case to verify the service is ok      

```

## Installation

To install the package, you can use Composer:

```bash
composer require wigl/wigl-sms-package
```

## Configuration

### Step 1: Publish the Config File

Once the package is installed, you can publish the config file to your application by running:

```bash
php artisan vendor:publish --provider="Wigl\WiglSmsPackage\WiglSmsPackageServiceProvider" --tag="config"
```
This will publish the configuration file config/wigl_sms.php.

### Step 2: Configure API Key and Other Settings
Open the config/services.php file and update the API key and other options:

```php
return [
    'sms_service' => [
        'api_key' => env('PRELUDE_API_KEY'),
        'service_activated' => env('SMS_SERVICE_ACTIVATED', true),
        'default_options' => [
            'locale' => 'en-US',
        ],
    ]
];
```
Ensure that the environment variables (PRELUDE_API_KEY, SMS_SERVICE_ACTIVATED) are added to your .env file.

## Usage
### Creating Verification Request
To send an SMS verification request to a phone number, use the createVerificationRequest method. This method accepts the phone number and an instance of VerificationOptions.

```php
use Wigl\WiglSmsPackage\SmsService;
use Wigl\WiglSmsPackage\DTO\VerificationOptions;

$smsService = new SmsService();

$options = new VerificationOptions();

// Send the verification request
$response = $smsService->createVerificationRequest('+1234567890', $options);

```
The createVerificationRequest method will send a request to the Prelude API to initiate an SMS verification for the provided phone number.

## Using Options and Signals
You can customize the VerificationOptions to send additional data such as locale, custom code, and device information (signals).

```php
$options = new VerificationOptions();
$options->setOptions(locale: 'fr-FR', customCode: '5678');
$options->setSignals(deviceId: 'device456', devicePlatform: 'ios', ip: '192.168.1.1', appVersion: '2.0.0');

```

### Options include:

- `locale` (e.g., `'en-US'`, `'fr-FR'`  BCP-47 format)
- `custom_code` (a custom code you want to use)

### Signals include:

- `device_id` (unique ID of the user's device)
- `device_platform` (e.g., `'android'`, `'ios'`, `'web'`)
- `ip` (user's IP address)
- `app_version` (the version of your app)

These values will be sent along with the phone number to the Prelude API.

## Example Request Payload

```json
{
  "target": {
    "type": "phone_number",
    "value": "+1234567890"
  },
  "options": {
    "locale": "en-US",
    "custom_code": "1234"
  },
  "signals": {
    "device_id": "device123",
    "device_platform": "android",
    "ip": "203.0.113.1",
    "app_version": "1.0.0"
  }
}

```

## Customization
### Modify Default Options
You can modify the default options by editing the config/wigl_sms.php file. The settings for locale, custom code, and device information can be adjusted globally in your config.

### Add More Fields
To extend the package with additional fields for your application (e.g., tracking user IDs, session IDs), you can modify the VerificationOptions class or extend it to meet your needs.

## Error Handling
In case of an error while sending the SMS, the SmsService will log the error and return false.

You can also handle specific errors using a custom error handler. The formatError function maps Prelude error codes to custom messages. To add or modify error codes, update the config/constants.php file.

```php
Log::channel('sms_mode')->error('Error: ' . $errorMessage);
```

## Testing

To run the tests for this package, follow these steps:

### 1. Set Up PHPUnit

Ensure that PHPUnit is installed and configured in your project. If it's not already installed, you can install it by running:

```bash
composer require --dev phpunit/phpunit ^11.5
```
### 2. Run PHPUnit
```bash
vendor/bin/phpunit
```

## Changelog
### Version 1.0.0
- `Initial release with support for SMS verification requests using the Prelude API.`
- `Support for dynamic options and signals configuration.`

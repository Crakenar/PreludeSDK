# Wigl Sms Package

The `Wigl Sms Package` provides an easy-to-use service for sending SMS verifications through the Prelude API. This package allows developers to send SMS verification codes to users' phone numbers and manage options and signals for enhanced functionality.

## Table of Contents

- [Package Structure](#package-structure)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
    - [Creating Verification Request](#creating-verification-request)
    - [Using Options and Signals](#using-options-and-signals)
    - [Validate Verification Code](#validate-verification-code)
- [Customization](#customization)
- [Error Handling](#error-handling)
    - [Custom](#custom-error-handling)
    - [Loging](#logging)
- [Testing](#testing)

## Package Structure
```bash
src/
├── app/
│   ├── Contracts/                  # Contains interfaces like PackageResponseInterface
│   ├── DTO/                        # Contains the application of interfaces like SmsPackageResponse.php for package user
│   ├── Enums/                      # Contains the status values that can be returned from the api
│   ├── Services/                   # Contains the logic for external services like SmsService
│   │   └── SmsService              # Your implementation of the api
│   ├── Utility/                    # Contains utility function used to for example, validate an E164 phone number format
│   └── tests/                      # Contains the logic for testing the package
│       └── Feature/            
│       └── Unit/
│           └── SmsServiceTest.php  # Default test case to verify the service is ok      
└── config/                         # Configuration files ! DO NOT DELETE, contains the list of known errors from the api
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
This will publish the configuration file config/services.php.

### Step 2: Configure API Key and Other Settings
Open the config/services.php file and update the API key and other options:

```php
return [
    'sms_service' => [
        'api_key' => env('PRELUDE_API_KEY'),
        'service_activated' => env('SMS_SERVICE_ACTIVATED', true),
        'default_options' => [
            'locale' => 'en-US', // Format BCP-47 mandatory
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

### Example Request Payload

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
### Validate Verification Code
Sends a request to verify the provided code for a phone number. This method accepts the phone number and a code string.

```php
use Wigl\WiglSmsPackage\SmsService;

$smsService = new SmsService();

// Send the code to the api to check his validity
$response = $smsService->createVerificationCheckCodeRequest('+1234567890', '1234');
```
### Example Request Payload

```json
{
  "target": {
    "type": "phone_number",
    "value": "+1234567890"
  },
  "code": "1234"
}
```

## Customization
### Modify Default Options
You can modify the default options by editing the `config/services.php` file. The settings for locale, custom code, and device information can be adjusted globally in your config.

### Add More Fields
To extend the package with additional fields for your application (e.g., tracking user IDs, session IDs), you can modify the `VerificationOptions` class or extend it to meet your needs.

## Error Handling
In case of an error while sending the SMS, the `SmsService` will log the error and return a `SmsPackageResponse` with an error message and code. Specifically, the `handleApiException` function is responsible for managing and formatting errors based on the response from the Prelude API or any exceptions thrown during the request.

If the exception contains a valid error response, `handleApiException` will parse the error code and message, format it, and return it in the response. If no exception is thrown, the method will handle a fallback generic error with a custom message.
### Custom Error Handling
The `handleApiException` function makes use of the formatError function to map Prelude error codes to custom error messages. You can update or add new error codes by modifying the relevant entries in the `config/constants.php` file.
```php
public function handleApiException()
```
### Logging
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
## Changelog

### Version 1.0.0
- **Initial release** with support for SMS verification requests using the Prelude API.
- **Dynamic configuration support** for options and signals used in verification requests.
- **Phone number validation** using E.164 format.
- **Error handling** integrated with `handleApiException` to process and log errors from the Prelude API.
- **Custom error codes** handled and formatted via `formatError` function, allowing easy customization in `config/constants.php`.
- **Logging support** for SMS-related errors and successful code validation through the `sms_mode` log channel.
- **SmsPackageResponse** as a unified response format for handling the success or failure of SMS verification requests.
- **Support for checking the status of verification codes** via the `createVerificationCheckCodeRequest` method.
- **Guzzle client integration** for sending API requests to the Prelude API and handling responses.
- **Error messages** properly formatted and logged to facilitate troubleshooting.


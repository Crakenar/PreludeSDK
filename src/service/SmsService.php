<?php

namespace Wigl\WiglSmsPackage\service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use Wigl\WiglSmsPackage\dto\CreateVerificationOptions;
use Wigl\WiglSmsPackage\interface\SmsServiceInterface;

class SmsService implements SmsServiceInterface
{
    protected bool $off = false;
    private string|null $apiKey;
    private bool $isServiceEnabled;
    private array $errorCodes;
    private Client $client;

    //todo use config php
    private const string URL_V2 = "https://api.prelude.dev/v2";

    public function __construct()
    {
        //Default SMS Service CONFIG
//        $this->ERROR_CODES = config('constants.prelude_error_codes');
        $this->off = config('services.sms_service.service_activated') || app()->env === 'testing';
        $this->apiKey = config('services.sms_service.api_key');
        $this->client = new Client();
    }

    // Setter for the client (for testing purposes)
    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    public function sendVerification(string $userPhoneNumber): bool
    {
//        if ($this->off) {
//            return true;
//        }
        try {
            $responseContent = json_decode($this->createVerificationRequest($userPhoneNumber)->getBody()->getContents());
            //If id then it means a session is opened on Prelude side for this phone number => success
            if ($responseContent->id) {
                Log::channel('sms_mode')->info('SMS successfully dispatched to ' . $userPhoneNumber);
                return true;
            }
//            $this->verifyPreludeSmsCode($appUserId);
            Log::channel('sms_mode')->error('Error with smsmode API request. Returned : ' . $responseContent);
            return false;
        } catch (\Exception $e) {
            dd($e);
            // expect a code, message, doc_url
            Log::channel('sms_mode')->error('Error with smsmode API request: ' . $exception->getMessage());

            $this->formatPreludeError('account_invalid', $e->getMessage());
            return false;
        } catch (GuzzleException $exception) {
            dd($exception);
            Log::channel('sms_mode')->error('Error with smsmode API request: ' . $exception->getMessage());
            // expect a code, message, doc_url
            $this->formatError('account_invalid', $exception->getMessage());
            return false;
        }
    }

    /**
     * This function use the Prelude API to send an SMS code to a phone number. A Session is created with the code / phone number on Prelude side
     * Atm we do not need to use a check code function because we have an api route ?? => ask Julien
     * @throws GuzzleException
     */
    /**
     * Creates a verification request to the Prelude API.
     *
     * @param string $phoneNumber The recipient's phone number.
     * @return ResponseInterface|string
     * @throws GuzzleException
     */
    public function createVerificationRequest(string $phoneNumber, CreateVerificationOptions $options): ResponseInterface|string
    {
        return $this->client->post(self::URL_V2 . '/verification', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => array_merge(
                [
                    'target' => [
                        'type' => 'phone_number',
                        'value' => $phoneNumber,
                    ],
                ],
                $options->toArray() // Merge in options and signals
            ),
        ]);
    }

    /**
     * Formats an error response for Prelude API errors.
     *
     * @param string $errorCode The specific error code.
     * @param string|null $message Additional error message details.
     * @return array Formatted error data.
     */
    public function formatError(string $errorCode, ?string $message = 'Unknown error'): array
    {
        return [
            'error_code' => $this->errorCodes[$errorCode] ?? 'Unknown error code.',
            'message' => $message,
        ];
    }
}

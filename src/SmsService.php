<?php

namespace Wigl\WiglSmsPackage;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string|null $apiKey = null;
    protected bool $off = false;
    protected mixed $ERROR_CODES = null;

    //todo use config php
    public const string URL_V2 = "https://api.prelude.dev/v2";

    public function __construct()
    {
        //Default SMS Service CONFIG
//        $this->ERROR_CODES = config('constants.prelude_error_codes');
        $this->off = config('sms.service_activated') || app()->env === 'testing';
        $this->apiKey = config('sms.api_key');;;
    }

    public function handle(string $userPhoneNumber): bool
    {
//        if ($this->off) {
//            return true;
//        }
        try {
            $responseContent = json_decode($this->apiRequestCreateVerification($userPhoneNumber)->getBody()->getContents());
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
            $this->formatPreludeError('account_invalid', $exception->getMessage());
            return false;
        }
    }

    /**
     * This function use the Prelude API to send an SMS code to a phone number. A Session is created with the code / phone number on Prelude side
     * Atm we do not need to use a check code function because we have an api route ?? => ask Julien
     * TODO OPTIONS :
     *  - options.locale for message language BCP-47 format (en-GB, fr-FR etc...) => user->language ?
     *  - options.custom_code to use a custom code instead of the one from Prelude ASk Julien how validate code is made
     *      -> on refait tout le systeme et on laisse Prelude tout gerer ?
     * TODO ANTI-FRAUD: need to add minimum these data for anti-fraud
     *  signals.app_version
     *  signals.device_id
     *  signals.device_platform (ENUM : android, ios, ipados, tvos, web)
     *  signals.ip
     * @throws GuzzleException
     */
    private function apiRequestCreateVerification(string $phoneNumber): \Psr\Http\Message\ResponseInterface|string
    {
        $client = new Client();
        return $client->post(self::URL_V2 . '/verification', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'target' => [
                    'type' => 'phone_number',
                    'value' => $phoneNumber,
                ],
//                'options' => [
//                    'locale' => 'en-GB',
//                    'custom_code' => '1234',
//                ],
//                'signals' => [
//                    'app_version' => '1.0.0',
//                    'device_id' => '123',
//                    'device_platform' => 'android',
//                    'ip' => '127.0.0.1',
//                ],
            ],
        ]);
    }

    private function formatPreludeError(string $errorCode, string $message = 'Unknown error'): array
    {

        $preludeErrorCodes = $this->ERROR_CODES['prelude_error_codes'];

        return [
            'error_code' => $preludeErrorCodes[$errorCode] ?? 'Unknown error code.',
            'errors' => $message
        ];
    }
}

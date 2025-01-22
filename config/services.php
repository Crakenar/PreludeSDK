<?php

return [
    'sms_service' => [
        'api_url' => env('SMS_API_URL'),
        'api_key' => env('SMS_API_KEY'),
        'service_activated' => env('SERVICE_ACTIVATED'),
        'default_options' => [
            'locale' => 'en-GB',
        ]
    ]
];

<?php

namespace Prelude\SmsSDK;

use Illuminate\Support\ServiceProvider;
use Prelude\SmsSDK\Services\SmsService;

class SmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SmsService::class, function () {
            return new SmsService();
        });
    }

    public function boot()
    {
        // Publish configuration if needed
        $this->publishes([
            __DIR__.'/../config/services.php' => config_path('services.php'),
            __DIR__.'/../config/constants.php.php' => config_path('constants.php'),
        ]);
    }
}

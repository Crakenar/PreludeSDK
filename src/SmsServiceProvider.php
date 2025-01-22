<?php

namespace Wigl\WiglSmsPackage;

use Illuminate\Support\ServiceProvider;
use Wigl\WiglSmsPackage\service\SmsService;

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
            __DIR__.'/../config/sms.php' => config_path('sms.php'),
        ]);
    }
}

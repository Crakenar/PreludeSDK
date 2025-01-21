<?php

namespace MyName\SmsService;

use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    public function register()
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

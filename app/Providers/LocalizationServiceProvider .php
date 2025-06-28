<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class LocalizationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Custom validation rule for supported locales
        Validator::extend('supported_locale', function ($attribute, $value, $parameters, $validator) {
            return in_array($value, config('app.supported_locales', ['en']));
        });

        // Custom validation message
        Validator::replacer('supported_locale', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, __('validation.supported_locale'));
        });
    }
}
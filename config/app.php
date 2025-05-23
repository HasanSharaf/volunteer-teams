<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | App\lication Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your App\lication. This value is used when the
    | framework needs to place the App\lication's name in a notification or
    | any other location as required by the App\lication or its packages.
    |
    */

    'name' => env('App_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | App\lication Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your App\lication is currently
    | running in. This may determine how you prefer to configure various
    | services the App\lication utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('App_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | App\lication Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your App\lication is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | App\lication. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('App_DEBUG', true),

    /*
    |--------------------------------------------------------------------------
    | App\lication URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your App\lication so that it is used when running Artisan tasks.
    |
    */

    'url' => env('App_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL'),

    /*
    |--------------------------------------------------------------------------
    | App\lication Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your App\lication, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | App\lication Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The App\lication locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the App\lication.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | App\lication Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your App\lication.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an App\lication!
    |
    */

    'key' => env('App_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => 'file',
        // 'store' => 'redis',
    ],

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your App\lication. Feel free to add your own services to
    | this array to grant expanded functionality to your App\lications.
    |
    */

    'providers' => ServiceProvider::defaultProviders()->merge([
        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
    ])->toArray(),

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this App\lication
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => Facade::defaultAliases()->merge([
        // 'Example' => App\Facades\Example::class,
    ])->toArray(),

];

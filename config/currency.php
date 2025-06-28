<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | This option controls the default currency used throughout the application.
    | You may change this to any of the currencies supported by your application.
    |
    */

    'default' => env('CURRENCY_DEFAULT', 'NGN'),

    /*
    |--------------------------------------------------------------------------
    | Currency Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure the currencies available in your application.
    | Each currency should have a symbol, name, and formatting options.
    |
    */

    'currencies' => [
        'NGN' => [
            'name' => 'Nigerian Naira',
            'symbol' => '₦',
            'code' => 'NGN',
            'decimals' => 2,
            'thousands_separator' => ',',
            'decimal_separator' => '.',
        ],
        'USD' => [
            'name' => 'US Dollar',
            'symbol' => '$',
            'code' => 'USD',
            'decimals' => 2,
            'thousands_separator' => ',',
            'decimal_separator' => '.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Display Settings
    |--------------------------------------------------------------------------
    |
    | These options control how currencies are displayed throughout the application.
    |
    */

    'display' => [
        'symbol_position' => 'before', // 'before' or 'after'
        'space_between' => false,
        'show_code' => false,
    ],

];
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | QR Code Settings
    |--------------------------------------------------------------------------
    |
    | Configure QR code generation and expiry settings for service check-in.
    |
    */

    // QR code expiry window (in minutes)
    'qr_expiry_before' => env('QR_EXPIRY_BEFORE', 15),
    'qr_expiry_after' => env('QR_EXPIRY_AFTER', 15),

    /*
    |--------------------------------------------------------------------------
    | Mobile Check-in Settings
    |--------------------------------------------------------------------------
    |
    | Configure mobile check-in behavior and geofencing rules.
    |
    */

    // Enable/disable mobile check-in functionality
    'enable_mobile_checkin' => env('ENABLE_MOBILE_CHECKIN', true),

    // Geofencing settings
    'require_geofencing' => env('REQUIRE_GEOFENCING', false),
    'allowed_distance' => env('ALLOWED_DISTANCE', 100), // Distance in meters

    // Church location coordinates
    'church_latitude' => env('CHURCH_LATITUDE'),
    'church_longitude' => env('CHURCH_LONGITUDE'),

    /*
    |--------------------------------------------------------------------------
    | QR Code Generation
    |--------------------------------------------------------------------------
    |
    | Settings for QR code generation and token management.
    |
    */

    // Token encryption key (must be 32 characters)
    'token_key' => env('QR_TOKEN_KEY', env('APP_KEY')),

    // Token format settings
    'token_length' => 32, // Length of the random token
    'token_algorithm' => 'sha256', // Hashing algorithm for token generation
];
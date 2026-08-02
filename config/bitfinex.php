<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Credentials
    |--------------------------------------------------------------------------
    |
    | Credentials issued at https://setting.bitfinex.com/api. They are only
    | required for authenticated endpoints; public endpoints work without them.
    |
    */

    'api_key' => env('BITFINEX_API_KEY'),

    'api_secret' => env('BITFINEX_API_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Base URLs
    |--------------------------------------------------------------------------
    |
    | Base URLs used for public and authenticated calls. Override them to point
    | the SDK at a proxy or a mock server; when empty, the values shipped with
    | the package are used.
    |
    */

    'urls' => [
        'public' => env('BITFINEX_PUBLIC_URL', 'https://api-pub.bitfinex.com'),
        'private' => env('BITFINEX_PRIVATE_URL', 'https://api.bitfinex.com'),
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Timeouts
    |--------------------------------------------------------------------------
    |
    | Request timeout, in seconds, for each client. Keep the authenticated
    | timeout generous: a timeout does not guarantee the order was not placed.
    |
    */

    'timeout' => [
        'public' => env('BITFINEX_PUBLIC_TIMEOUT', 10.0),
        'authenticated' => env('BITFINEX_AUTHENTICATED_TIMEOUT', 30.0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Token
    |--------------------------------------------------------------------------
    |
    | Defaults applied when generating short-lived tokens through
    | Bitfinex::authenticated()->generateToken().
    |
    */

    'token' => [
        'scope' => env('BITFINEX_TOKEN_SCOPE', 'api'),
        'ttl' => env('BITFINEX_TOKEN_TTL', 120),
        'write_permission' => env('BITFINEX_TOKEN_WRITE_PERMISSION', false),
    ],

];

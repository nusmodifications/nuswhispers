<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'mandrill' => [
        'secret' => env('MANDRILL_SECRET', ''),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => env('SES_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => NUSWhispers\Models\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    'recaptcha' => [
        'key' => env('RECAPTCHA_SECRET', '6LcUdQMTAAAAAD_LA9uHt2DQgiJ24zBpN64KBGIh'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_APP_ID', ''),
        'client_secret' => env('FACEBOOK_APP_SECRET', ''),
        'page_id' => env('FACEBOOK_PAGE_ID', ''),
        'redirect' => env('APP_URL', 'http://www.nuswhispers.com') . '/admin/profile/connect/facebook',
    ],

];

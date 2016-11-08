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
        'domain' => env('MAILGUN_DOMAIN', ''),
        'secret' => env('MAILGUN_SECRET', ''),
    ],

    'mandrill' => [
        'secret' => env('MANDRILL_SECRET', ''),
    ],

    'ses' => [
        'key' => '',
        'secret' => '',
        'region' => 'us-east-1',
    ],

    'stripe' => [
        'model' => 'User',
        'secret' => '',
    ],

    'reCAPTCHA' => [
        'key' => env('RECAPTCHA_SECRET', '6LcUdQMTAAAAAD_LA9uHt2DQgiJ24zBpN64KBGIh'),
        'verify' => 'https://www.google.com/recaptcha/api/siteverify?secret=%s&response=%s',
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_APP_ID', ''),
        'client_secret' => env('FACEBOOK_APP_SECRET', ''),
        'page_id' => env('FACEBOOK_PAGE_ID', ''),
        'redirect' => env('APP_URL', 'http://www.nuswhispers.com') . '/admin/profile/connect/facebook',
    ],

];

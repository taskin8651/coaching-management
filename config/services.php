<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    '11za' => [
        'enabled' => env('WHATSAPP_ENABLED', false),
        'api_url' => env(
            'WHATSAPP_11ZA_API_URL',
            'https://api.11za.in/apis/template/sendTemplate'
        ),
        'auth_token' => env('WHATSAPP_11ZA_AUTH_TOKEN'),
        'origin_website' => env(
            'WHATSAPP_11ZA_ORIGIN_WEBSITE',
            'https://karmayogaacademy.com/'
        ),
        'language' => env('WHATSAPP_11ZA_LANGUAGE', 'en'),
    ],

];

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, SparkPost and others. This file provides a sane default
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'facebook' => [
     'client_id' => env('FACEBOOK_CLIENT_ID'),      
     'client_secret' => env('FACEBOOK_CLIENT_SECRET'),   
     'redirect' => env('FACEBOOK_CALLBACK_URL'),        
   ],    

   'github' => [
    'client_id' => env('GITHUB_CLIENT_ID'), // Your GitHub Client ID
    'client_secret' => env('GITHUB_CLIENT_SECRET'), // Your GitHub Client Secret
    'redirect' => env('GITHUB_CALLBACK_URL'),
    ],   

    'linkedin' => [      
    'client_id' => env('LINKEDIN_CLIENT_ID'), // Your GitHub Client ID
    'client_secret' => env('LINKEDIN_CLIENT_SECRET'), // Your GitHub Client Secret
    'redirect' => env('LINKEDIN_CALLBACK_URL'),
    ],  

    'twitter' => [
    'client_id' => env('TWITTER_CLIENT_ID'),
    'client_secret' => env('TWITTER_CLIENT_SECRET'),
    'redirect' => env('TWITTER_CALLBACK_URL'),
    ],

    'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_CALLBACK_URL'),  
      ],

    'bitbucket' => [
    'client_id' => env('BITBUCKET_CLIENT_ID'),
    'client_secret' => env('BITBUCKET_CLIENT_SECRET'),
    'redirect' => env('BITBUCKET_CALLBACK_URL'),    
      ],
];

<?php

return [
    'client_id' => env('QBO_CLIENT_ID'),
    'client_secret' => env('QBO_CLIENT_SECRET'),
    'redirect_uri' => env('QBO_REDIRECT_URI', env('APP_URL').'/integrations/qbo/callback'),
    'environment' => env('QBO_ENV', 'sandbox'), // sandbox|production
    'scopes' => [
        'com.intuit.quickbooks.accounting',
        'openid',
        'profile',
        'email',
        'phone',
        'address',
    ],
];

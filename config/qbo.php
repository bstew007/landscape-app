<?php

return [
    'client_id' => env('QBO_CLIENT_ID'),
    'client_secret' => env('QBO_CLIENT_SECRET'),
    'redirect_uri' => env('QBO_REDIRECT_URI', env('APP_URL').'/integrations/qbo/callback'),
    'environment' => env('QBO_ENV', 'sandbox'), // sandbox|production
    'webhook_token' => env('QBO_WEBHOOK_TOKEN'),
    'auto_sync' => env('QBO_AUTO_SYNC', true),
    'scopes' => [
        'com.intuit.quickbooks.accounting',
        'openid',
        'profile',
        'email',
        'phone',
        'address',
    ],
];

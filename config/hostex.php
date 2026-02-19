<?php

return [
    'api_key' => env('HOSTEX_API_KEY'),
    'base_url' => env('HOSTEX_BASE_URL', 'https://api.hostex.io/v3'),
    'webhook_secret' => env('HOSTEX_WEBHOOK_SECRET'),
];

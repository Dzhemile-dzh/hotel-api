<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PMS API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Property Management System (PMS) API integration.
    |
    */

    'api' => [
        'base_url' => env('PMS_API_BASE_URL', 'https://api.pms.donatix.info/api'),
        'rate_limit' => env('PMS_API_RATE_LIMIT', 2), // requests per second
        'timeout' => env('PMS_API_TIMEOUT', 30), // seconds
        'retry_attempts' => env('PMS_API_RETRY_ATTEMPTS', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sync Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the synchronization process.
    |
    */

    'sync' => [
        'batch_size' => env('PMS_SYNC_BATCH_SIZE', 100),
        'max_concurrent_requests' => env('PMS_MAX_CONCURRENT_REQUESTS', 1),
        'log_level' => env('PMS_LOG_LEVEL', 'info'),
    ],
]; 
<?php

return [
    'driver' => env('ITL_DRIVER', 'fake'), // fake | http
    'base_url' => env('ITL_BASE_URL', 'https://api.nfz.gov.pl/app-itl-api'),
    'timeout' => (int) env('ITL_HTTP_TIMEOUT', 6), // sek.
    'retry' => [
        'times' => (int) env('ITL_HTTP_RETRY_TIMES', 2),
        'sleep_ms' => (int) env('ITL_HTTP_RETRY_SLEEP_MS', 200),
    ],
    // czas życia cache (sekundy) – domyślnie 15 min
    'cache_ttl' => (int) env('ITL_CACHE_TTL', 900),
];

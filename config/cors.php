<?php

return [

    // Jakie ścieżki obejmuje CORS
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],
    'allowed_headers' => ['*'],

    // Na DEV najlepiej wskazać frontanda (VITE_API_URL), na PROD docelowy URL frontendu
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],
    'allowed_origins_patterns' => [],

    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];

<?php

$allowedOrigins = array_filter(array_map('trim', explode(',', (string) env('API_ALLOWED_ORIGINS', ''))));

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => $allowedOrigins !== [] ? $allowedOrigins : ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Authorization', 'Content-Type', 'Accept', 'X-Requested-With', 'Origin'],
    'exposed_headers' => ['Link'],
    'max_age' => 600,
    'supports_credentials' => false,
];

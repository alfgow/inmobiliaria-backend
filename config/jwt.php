<?php

return [
    'secret' => env('API_JWT_SECRET', ''),
    'ttl' => (int) env('API_JWT_TTL', 3600),
    'issuer' => env('API_JWT_ISSUER', env('APP_URL')),
];

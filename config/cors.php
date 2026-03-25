<?php

$origins = env('FLEET_API_CORS_ORIGINS');

return [

    'paths' => ['api/fleet/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => (! is_string($origins) || trim($origins) === '')
        ? ['*']
        : array_values(array_filter(array_map('trim', explode(',', $origins)))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];

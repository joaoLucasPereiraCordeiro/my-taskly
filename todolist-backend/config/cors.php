<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

 'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_methods' => ['*'],
'allowed_origins' => [
    'https://my-taskly-teal.vercel.app',
    'http://localhost:5173',
    'http://localhost:8080',
],
'allowed_headers' => ['*'],
'supports_credentials' => true,

    'exposed_headers' => ['Authorization'],
    'max_age' => 0,
];

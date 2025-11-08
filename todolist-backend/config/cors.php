<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://my-taskly-teal.vercel.app',
        'https://my-taskly-g79ptsw12-joao-lucas-projects-1bb67238.vercel.app',
        'http://localhost:5173',
        'http://localhost:8080',
    ],

    'allowed_headers' => ['*'],
    'exposed_headers' => ['Authorization'],
    'max_age' => 0,
    'supports_credentials' => true,
];

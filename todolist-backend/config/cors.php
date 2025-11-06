<?php

return [

    'paths' => ['api/*', 'login', 'register', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:8080', // para desenvolvimento local
        'https://my-taskly-j0pehcjg5-joao-lucas-projects-1bb67238.vercel.app', // URL Vercel
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];

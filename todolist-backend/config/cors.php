<?php

return [

    'paths' => ['api/*', 'login', 'register', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:8080', // desenvolvimento local
        'https://my-taskly-teal.vercel.app', // seu novo domínio no Vercel
        'https://my-taskly-frontend-lk64pdsxp-joao-lucas-projects-1bb67238.vercel.app', // antigo (opcional)
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];

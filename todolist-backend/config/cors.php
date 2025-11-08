<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://my-taskly-g79ptsw12-joao-lucas-projects-1bb67238.vercel.app',
        'http://localhost:5173', // se for testar local
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Authorization'],

    'supports_credentials' => true,
];

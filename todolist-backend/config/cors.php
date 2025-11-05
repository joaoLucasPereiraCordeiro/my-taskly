<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS Configuration
    |--------------------------------------------------------------------------
    |
    | Esse arquivo controla quais origens podem acessar sua API.
    | Aqui permitimos o localhost para desenvolvimento e
    | qualquer subdomínio do seu app hospedado na Vercel.
    |
    */

    'paths' => ['api/*', 'login', 'register', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // 🔥 Permite o localhost e QUALQUER subdomínio da Vercel
    'allowed_origins' => [
        'http://localhost:8080',
    ],

    // 🔥 Padrão para aceitar qualquer domínio *.vercel.app
    'allowed_origins_patterns' => [
        '/^https:\/\/my-taskly-frontend.*\.vercel\.app$/',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Deixe false se não usa cookies / sessões
    'supports_credentials' => false,

];

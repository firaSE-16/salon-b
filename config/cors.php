<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
   'allowed_origins' => [
    // Local development
    'http://localhost:5173',
    'http://localhost:3000',
    // Your Vercel domains
    'https://salon-a.vercel.app',
    'https://salon-a-git-main-loariftech-4320s-projects.vercel.app',
    'https://salon-l50b4hkfp-loariftech-4320s-projects.vercel.app',
],
'allowed_origins_patterns' => [
    '/^https:\/\/.*vercel\.app$/',
],
    'allowed_headers' => [
        'Content-Type',
        'X-Requested-With',
        'Authorization',
        'X-Salon-Id',
        'Accept',
        'Origin',
    ],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];

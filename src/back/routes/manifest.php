<?php

include_once AuWebRoot . '/src/back/import/pages.php';
include_once AuWebRoot . '/src/back/labels/HttpStatuses.php';

$server = Server::getInstance();
$router = $server->router();
$logger = $server->logger();


$router->respond('GET', '/manifest.webmanifest', function ($request, $response) {
    $response->json([
        'name' => Localization['pwa.company.name'],
        'short_name' => Localization['pwa.company.name.short'],
        'start_url' => '/',
        'display' => 'standalone',
        'background_color' => '#17A086',
        'description' => Localization['pwa.company.description'],
        'lang' => 'ru-RU',
        'icons' => [
            ['src' => 'images/favicon-48.png',
                'sizes' => '48x48',
                'type' => 'image/png'
            ],
            [
                'src' => 'images/favicon-72.png',
                'sizes' => '72x72',
                'type' => 'image/png'
            ],
            [
                'src' => 'images/favicon-96.png',
                'sizes' => '96x96',
                'type' => 'image/png'
            ],
            [
                'src' => 'images/favicon-144.png',
                'sizes' => '144x144',
                'type' => 'image/png'
            ],
            [
                'src' => 'images/favicon-168.png',
                'sizes' => '168x168',
                'type' => 'image/png'
            ],
            [
                'src' => 'images/favicon-192.png',
                'sizes' => '192x192',
                'type' => 'image/png'
            ]
        ]
    ]);
});

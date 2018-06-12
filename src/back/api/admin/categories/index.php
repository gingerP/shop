<?php

include_once AuWebRoot . '/src/back/authenticate/UserPasswordAuthenticate.php';

$server = Server::getInstance();
$router = $server->router();
$authenticator = new UserPasswordAuthenticate();

$router->respond('GET', '/api/admin/categories', function ($request, $response) use ($authenticator, $server) {
    $server->assertIsSecure($request);
    $authenticator->authenticate($request, $response);

    $responseData = GoodsKeysService::getList();

    $response->json($responseData);
});

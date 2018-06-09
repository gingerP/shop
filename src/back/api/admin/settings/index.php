<?php

include_once AuWebRoot . '/src/back/authenticate/UserPasswordAuthenticate.php';

$server = Server::getInstance();
$router = $server->router();

$authenticator = new UserPasswordAuthenticate();

$router->respond('GET', '/api/admin/settings', function ($request, $response) use ($authenticator) {
    $authenticator->secure($request)->authenticate($request, $response);

    $responseData = PreferencesService::getAdminPreferences();
    $response->json($responseData);
});

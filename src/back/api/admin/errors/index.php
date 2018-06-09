<?php

include_once AuWebRoot . '/src/back/authenticate/UserPasswordAuthenticate.php';

$server = Server::getInstance();
$router = $server->router();

$authenticator = new UserPasswordAuthenticate();
$router->respond('POST', '/api/admin/errors', function ($request, $response) use ($authenticator) {
    $authenticator->secure($request)->authenticate($request, $response);

    $body = json_decode($request->body(), true);
    $message = $body['message'];
    $stack = $body['stack'];
    $pageUrl = $body['pageUrl'];
    ErrorsService::saveError($message, $stack, $pageUrl);

    $response->send();
});

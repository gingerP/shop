<?php

include_once AuWebRoot . '/src/back/errors/UserPasswordInvalidError.php';
include_once AuWebRoot . '/src/back/authenticate/UserPasswordAuthenticate.php';

$server = Server::getInstance();
$router = $server->router();
$authenticator = new UserPasswordAuthenticate();

$router->respond('POST', '/api/admin/auth', function ($request, $response, $service) use ($authenticator, $server) {
    $server->assertIsSecure($request);
    $body = json_decode($request->body(), true);

    $service->validate($body['username'], (new UserPasswordInvalidError())->getMessage())->isLen(5, 64)->isChars('a-zA-Z0-9-');
    $service->validate($body['password'])->notNull();

    $userName = $body['username'];
    $password = $body['password'];

    if (AuthManager::checkUserPasswd($userName, $password)) {
        $response->json([
            'token' => $authenticator->token($userName),
            'refreshToken' => $authenticator->refreshToken($userName)
        ]);
        return;
    }

    throw new UserPasswordInvalidError();
});

$router->respond('POST', '/api/admin/auth/refresh', function ($request, $response, $service) use ($authenticator, $server) {
    $server->assertIsSecure($request);
    $body = json_decode($request->body(), true);
    $service->validate($body['refreshToken'])->notNull();
    $refreshToken = $body['refreshToken'];
    $decoded = $authenticator->validateRefreshToken($refreshToken);
    $response->json([
        'token' => $authenticator->token($decoded['user'])
    ]);
});

$router->respond('GET', '/api/admin/auth/check', function ($request, $response) use ($authenticator, $server) {
    $server->assertIsSecure($request);
    $authenticator->authenticate($request, $response);
    $response->json([]);
});

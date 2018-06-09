<?php

include_once AuWebRoot . '/src/back/authenticate/UserPasswordAuthenticate.php';

$server = Server::getInstance();
$router = $server->router();

$authenticator = new UserPasswordAuthenticate();

$router->respond('GET', '/api/admin/booklets/?', function ($request, $response) use ($authenticator) {
    $authenticator->secure($request)->authenticate($request, $response);

    $mappingString = $request->param('mapping');
    $mapping = isset($mappingString) ? json_decode($mappingString, true) : [];
    $responseData = BookletService::getList($mapping);

    $response->json($responseData);
});

$router->respond('GET', '/api/admin/booklets/[i:id]/?', function ($request, $response) use ($authenticator) {
    $authenticator->secure($request)->authenticate($request, $response);

    $mappingString = $request->param('mapping');
    $mapping = isset($mappingString) ? json_encode($mappingString) : [];
    $id = intval($request->param('id'));
    $responseData = BookletService::get($id, $mapping);

    $response->json($responseData);
});

$router->respond('POST', '/api/admin/booklets/?', function ($request, $response) use ($authenticator) {
    $authenticator->secure($request)->authenticate($request, $response);

    $data = json_decode($request->body(), true);
    $responseData = BookletService::save($data);

    $response->json($responseData);
});

$router->respond('DELETE', '/api/admin/booklets/[i:id]/?', function ($request, $response) use ($authenticator) {
    $authenticator->secure($request)->authenticate($request, $response);

    $id = intval($request->param('id'));
    $responseData = BookletService::delete($id);

    $response->json($responseData);
});

$router->respond('GET', '/api/admin/booklets/backgrounds/?', function ($request, $response) use ($authenticator) {
    $authenticator->secure($request)->authenticate($request, $response);

    $responseData = BookletService::getBookletBackgroundImages();

    $response->json($responseData);
});
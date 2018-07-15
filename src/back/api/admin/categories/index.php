<?php

include_once AuWebRoot . '/src/back/authenticate/UserPasswordAuthenticate.php';
include_once AuWebRoot . '/src/back/services/CategoriesService.php';

$server = Server::getInstance();
$router = $server->router();
$authenticator = new UserPasswordAuthenticate();

$router->respond('GET', '/api/admin/categories', function ($request, $response) use ($authenticator, $server) {
    $server->assertIsSecure($request);
    $authenticator->authenticate($request, $response);

    $responseData = CategoriesService::getList();

    $response->json($responseData);
});

$router->respond('POST', '/api/admin/categories', function ($request, $response) use ($authenticator, $server) {
    $server->assertIsSecure($request);
    $authenticator->authenticate($request, $response);

    $category = json_decode($request->body(), true);

    $updatedCategory = CategoriesService::saveCategory($category);

    $response->json($updatedCategory);
});

$router->respond('DELETE', '/api/admin/categories/[i:id]', function ($request, $response) use ($authenticator, $server) {
    $server->assertIsSecure($request);
    $authenticator->authenticate($request, $response);

    $id = intval($request->param('id', -1));

    $updatedCategory = CategoriesService::removeCategory($id);

    $response->json($updatedCategory);
});

<?php
use Webmozart\Assert\Assert;
include_once AuWebRoot . '/src/back/authenticate/UserPasswordAuthenticate.php';

$server = Server::getInstance();
$router = $server->router();

$authenticator = new UserPasswordAuthenticate();

$router->respond('POST', '/api/admin/products/?', function ($request, $response) use ($authenticator, $server) {
    $server->assertIsSecure($request);
    $authenticator->authenticate($request, $response);

    $data = json_decode($request->body(), true);
    if (isset($data['id'])) {
        Assert::string($data['id']);
    }
    if (isset($data['key_item'])) {
        Assert::string($data['key_item']);
    }
    Assert::string($data['name']);
    Assert::string($data['category']);
    Assert::isArray($data['description']);
    $id = $data['id'];
    $responseData = ProductsService::updateGood($id, $data);

    $response->json($responseData);
});

$router->respond('GET', '/api/admin/products', function ($request, $response) use ($authenticator, $server) {
    $server->assertIsSecure($request);
    $authenticator->authenticate($request, $response);

    $responseData = ProductsService::getGoods(-1);

    $response->json($responseData);
});

$router->respond('GET', '/api/admin/products/[i:id]/?', function ($request, $response) use ($authenticator, $server) {
    $server->assertIsSecure($request);
    $authenticator->authenticate($request, $response);

    $id = intval($request->param('id', -1));
    $responseData = ProductsService::getProduct($id);

    $response->json($responseData);
});

$router->respond('DELETE', '/api/admin/products/[i:id]/?', function ($request, $response) use ($authenticator, $server) {
    $server->assertIsSecure($request);
    $authenticator->authenticate($request, $response);

    $id = intval($request->param('id', -1));
    $responseData = ProductsService::deleteGood($id);

    $response->json($responseData);
});

$router->respond('GET', '/api/admin/products/order/?', function ($request, $response) use ($authenticator, $server) {
    $server->assertIsSecure($request);
    $authenticator->authenticate($request, $response);

    $responseData = ProductsService::getGoodsOrder();

    $response->json($responseData);
});

$router->respond('POST', '/api/admin/products/order/?', function ($request, $response) use ($authenticator, $server) {
    $server->assertIsSecure($request);
    $authenticator->authenticate($request, $response);

    $data = json_decode($request->body(), true);
    $responseData = ProductsService::saveGoodsOrder($data);

    $response->json($responseData);
});

$router->respond('GET', '/api/admin/products/next_code/[a:code]/?', function ($request, $response) use ($authenticator, $server) {
    $server->assertIsSecure($request);
    $authenticator->authenticate($request, $response);

    $code = intval($request->param('code'));
    $responseData = ProductsService::getNextGoodCode($code);

    $response->json($responseData);
});

$router->respond('POST', '/api/admin/products/[i:id]/images/?', function ($request, $response) use ($authenticator, $server) {
    $server->assertIsSecure($request);
    $authenticator->authenticate($request, $response);

    $id = intval($request->param('id'));
    $data = json_decode($request->body(), true);
    ProductsService::updateImages($id, $data);

    $response->json([]);
});

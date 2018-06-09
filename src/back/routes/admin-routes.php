<?php

include_once AuWebRoot . '/src/back/views/admin/AdminPageProducts.php';
include_once AuWebRoot . '/src/back/views/admin/AdminPageCloud.php';
include_once AuWebRoot . '/src/back/views/admin/AdminPageBooklets.php';
include_once AuWebRoot . '/src/back/views/admin/AdminPageSettingsPage.php';
include_once AuWebRoot . '/src/back/authenticate/UserPasswordAuthenticate.php';

$server = Server::getInstance();
$router = $server->router();
$authenticator = new UserPasswordAuthenticate();

$router->respond('GET', '/admin/?', function ($request, $response) use ($authenticator) {
    $authenticator->secure($request);
    $page = new AdminPageProducts();
    $response->headers([
        'Content-Type' => 'text/html; charset=utf-8'
    ]);
    $response->body($page->build()->getContent());
    $response->send();
});

$router->respond('GET', '/admin/products/?', function ($request, $response) use ($authenticator) {
    $authenticator->secure($request);
    $page = new AdminPageProducts();
    $response->headers([
        'Content-Type' => 'text/html; charset=utf-8'
    ]);
    $response->body($page->build()->getContent());
    $response->send();
});

$router->respond('GET', '/admin/cloud/?', function ($request, $response) use ($authenticator) {
    $authenticator->secure($request);
    $page = new AdminPageCloud();
    $response->headers([
        'Content-Type' => 'text/html; charset=utf-8'
    ]);
    $response->body($page->build()->getContent());
    $response->send();
});

$router->respond('GET', '/admin/booklets/?', function ($request, $response) use ($authenticator) {
    $authenticator->secure($request);
    $page = new AdminPageBooklets();
    $response->headers([
        'Content-Type' => 'text/html; charset=utf-8'
    ]);
    $response->body($page->build()->getContent());
    $response->send();
});

$router->respond('GET', '/admin/settings/?', function ($request, $response) use ($authenticator) {
    $authenticator->secure($request);
    $page = new AdminPageSettingsPage();
    $response->headers([
        'Content-Type' => 'text/html; charset=utf-8'
    ]);
    $response->body($page->build()->getContent());
    $response->send();
});

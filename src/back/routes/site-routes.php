<?php

include_once AuWebRoot . '/src/back/import/pages.php';
include_once AuWebRoot . '/src/back/labels/HttpStatuses.php';

$server = Server::getInstance();
$router = $server->router();
$logger = $server->logger();

$router->respond('GET', '/?', function ($request, $response) {
    $page = new MainPage();
    $page->validate($request);
    $response->headers([
        'Content-Type' => 'text/html; charset=utf-8'
    ]);
    $response->body($page->build()->getContent());
    $response->send();
});

$router->respond('GET', '/' . UrlParameters::PAGE__CATALOG . '/[:category]?/?', function ($request, $response) {
    $page = new CatalogPage($request);
    $page->validate($request);
    $response->headers([
        'Content-Type' => 'text/html; charset=utf-8'
    ]);
    $response->body($page->build()->getContent());
    $response->send();
});

$router->respond('GET', '/' . UrlParameters::PAGE__CONTACTS, function ($request, $response) {
    $page = new ContactsPage($request);
    $page->validate($request);
    $response->headers([
        'Content-Type' => 'text/html; charset=utf-8'
    ]);
    $response->body($page->build()->getContent());
    $response->send();
});

$router->respond('GET', '/' . UrlParameters::PAGE__DELIVERY, function ($request, $response) {
    $page = new DeliveryPage($request);
    $page->validate($request);
    $response->headers([
        'Content-Type' => 'text/html; charset=utf-8'
    ]);
    $response->body($page->build()->getContent());
    $response->send();
});

$router->respond('GET', '/' . UrlParameters::PAGE__PRODUCTS . '/[:productCode]/?', function ($request, $response) {
    $page = new ProductPage($request);
    $page->validate($request);
    $response->headers([
        'Content-Type' => 'text/html; charset=utf-8'
    ]);
    $response->body($page->build()->getContent());
    $response->send();
});

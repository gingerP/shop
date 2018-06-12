<?php

include_once AuWebRoot . '/src/back/import/pages.php';
include_once AuWebRoot . '/src/back/labels/HttpStatuses.php';

$server = Server::getInstance();
$router = $server->router();
$logger = $server->logger();

$router->respond('GET', '/?', function ($request, $response) {
    //deprecated url schema
    $pageName = $request->param('page_name');
    if (!is_null($pageName)) {
        switch($pageName) {
            case 'singleItem':
                $pageProductCode = $request->param('page_id');
                if (!is_null($pageName) && !is_null($pageProductCode)) {
                    $response->status(HttpStatuses::MOVED_PERMANENTLY);
                    $response->header('Location', URLBuilder::getCatalogLinkForSingleItem($pageProductCode));
                    return;
                }
                break;
            case 'contacts':
                $response->status(HttpStatuses::MOVED_PERMANENTLY);
                $response->header('Location', URLBuilder::getContactsLink());
                return;
            case 'catalog':
                $category = $request->param('key');
                $pageSize = $request->param('items_count');
                $pageNum = $request->param('page_num');
                if ($category === 'GN') {
                    $response->status(HttpStatuses::MOVED_PERMANENTLY);
                    $response->header('Location', URLBuilder::getCatalogLink($pageNum, $pageSize));
                    return;
                } else {
                    $response->status(HttpStatuses::MOVED_PERMANENTLY);
                    $response->header('Location', URLBuilder::getCatalogLinkForCategory($category, $pageNum, $pageSize));
                    return;
                }
                break;
            case 'delivery':
                $response->status(HttpStatuses::MOVED_PERMANENTLY);
                $response->header('Location', URLBuilder::getDeliveryLink());
                return;
        }
    }

    //actual url schema
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

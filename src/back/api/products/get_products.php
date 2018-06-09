<?php

$server = Server::getInstance();
$router = $server->router();

$router->respond('GET', '/api/products', function($request, $response, $service) {
    //$searchValue, $page = 0, $limit = 10, $includeNav = true, $includeContacts = true, $shouldNormalize = true
    $searchValue = $request->param('search', '');
    $pageNumber = intval($request->param(UrlParameters::PAGE_NUM, 1));
    $itemsCount = intval($request->param(UrlParameters::ITEMS_COUNT, Labels::VIEW_MODE_NUMERIC_DEF));
    $acceptData = $request->headers()['Accept'];
    if (strpos($acceptData, 'text/html') === 0) {
        $responseHtml = SearchService::searchAsHtml($searchValue, $pageNumber, $itemsCount, true, true, true);
        $response->body($responseHtml);
        $response->send();
    } else {
        $response->json(SearchService::search($searchValue, $pageNumber, $itemsCount, true, true, true));
    }
});

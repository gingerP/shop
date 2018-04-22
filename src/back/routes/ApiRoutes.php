<?php
include_once AuWebRoot.'/src/back/import/db.php';
include_once AuWebRoot.'/src/back/import/services.php';
class ApiRoutes {
    function __construct(&$klein)
    {
        $klein->respond('GET', '/api/contacts', function($request, $response) {
            $response->json(AddressService::getAddresses());
        });

        $klein->respond('GET', '/api/search', function($request, $response, $service) {
            $service->validateParam('search')->
            //$searchValue, $page = 0, $limit = 10, $includeNav = true, $includeContacts = true, $shouldNormalize = true
            $searchValue = $request->param('search', '');
            $pageNum = intval($request->param(UrlParameters::PAGE_NUM, 1));
            $itemsCount = intval($request->param(UrlParameters::ITEMS_COUNT, Labels::VIEW_MODE_NUMERIC_DEF));
            $this->searchValue = $request->param(UrlParameters::SEARCH_VALUE, '');

            if (substr($request->headers()['Content-Type'], 0, 10) == 'text/html;') {
                $response->send(SearchService::searchAsHtml());
            } else {
                $response->json(SearchService::search());
            }
        });
    }
}
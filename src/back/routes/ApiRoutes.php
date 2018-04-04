<?php
include_once('src/back/import/db');
include_once('src/back/import/service');
class ApiRoutes {
    function __construct(&$klein)
    {
        $klein->respond('GET', '/api/contacts', function($request, $response) {
            $response->json(AddressService::getAddresses());
        });
    }
}
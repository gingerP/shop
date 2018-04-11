<?php
include_once AuWebRoot.'/src/back/import/db.php';
include_once AuWebRoot.'/src/back/import/services.php';
class ApiRoutes {
    function __construct(&$klein)
    {
        $klein->respond('GET', '/api/contacts', function($request, $response) {
            $response->json(AddressService::getAddresses());
        });
    }
}
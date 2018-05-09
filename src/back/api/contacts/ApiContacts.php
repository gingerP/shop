<?php

class ApiContacts {
    function __construct(&$klein, &$logger)
    {
        $klein->respond('GET', '/api/contacts', function($request, $response) {
            $response->json(AddressService::getAddresses());
        });
    }
}
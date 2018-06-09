<?php

$server = Server::getInstance();
$router = $server->router();

$router->respond('GET', '/api/contacts', function($request, $response) {
    $response->json(AddressService::getAddresses());
});

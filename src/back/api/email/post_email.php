<?php

$server = Server::getInstance();
$router = $server->router();

$router->respond('POST', '/api/email', function($request, $response) {
    $body = json_decode($request->body(), true);
    $message = $body['message'];
    $name = $body['name'];
    $email = $body['email'];
    $product = $body['product'];
    $response->json(EmailService::sendFeedbackEmail($message, $email, $name, $product));
});

<?php

class ApiEmail {
    function __construct(&$klein, &$logger)
    {
        $klein->respond('POST', '/api/email', function($request, $response) use ($logger) {
            $body = json_decode($request->body(), true);
            $message = $body['message'];
            $name = $body['name'];
            $email = $body['email'];
            $product = $body['product'];
            $response->json(EmailService::sendFeedbackEmail($message, $email, $name, $product));
        });
    }
}
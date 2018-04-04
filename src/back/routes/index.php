<?php
include_once 'src/back/import/import';
include_once 'src/back/import/page';
include_once 'src/back/labels/HttpStatuses.php';
require 'src/back/routes/ApiRoutes.php';
require 'src/back/routes/SiteRoutes.php';
require 'src/back/routes/AdminRoutes.php';

use Katzgrau\KLogger\Logger as Logger;
$klein = new \Klein\Klein();
$logger = new Logger(AU_CONFIG['log.file'], Psr\Log\LogLevel::DEBUG);

$klein->respond(function ($request, $response, $service, $app) use ($klein, $logger) {
    // Handle exceptions => flash the message and redirect to the referrer
    $klein->onError(function ($klein, $error) use ($response, $logger) {
        echo 'ssssssssssss2';
        if ($error instanceof ProductNotFoundError) {
            //sendNotFoundPage();
            return;
        } else if ($error instanceof BaseError) {
            $response->code($error->status);
            $response->json($error->toJson());
            $logger->error($error->toJson());
            return;
        }
        $internalError = new InternalError($error);
        $response->code(HttpStatuses::INTERNAL_SERVER_ERROR);
        $response->json($internalError->toJson());
        $logger->error($internalError);
    });
});

new SiteRoutes($klein);
new ApiRoutes($klein);
new AdminRoutes($klein);

$klein->dispatch();
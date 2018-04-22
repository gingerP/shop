<?php
include_once AuWebRoot . '/src/back/import/import.php';
include_once AuWebRoot . '/src/back/import/pages.php';
include_once AuWebRoot . '/src/back/labels/HttpStatuses.php';
require AuWebRoot . '/src/back/routes/ApiRoutes.php';
require AuWebRoot . '/src/back/routes/SiteRoutes.php';
require AuWebRoot . '/src/back/routes/AdminRoutes.php';

use Katzgrau\KLogger\Logger as Logger;

$klein = new \Klein\Klein();
$logger = new Logger(AU_CONFIG['log.file'], Psr\Log\LogLevel::DEBUG);
$klein->respond(function ($request, $response) {
    $start = microtime();
});

new SiteRoutes($klein);
new ApiRoutes($klein);
new AdminRoutes($klein);

$klein->respond(function ($request, $response, $service, $app) use ($klein, $logger) {
    // Handle exceptions => flash the message and redirect to the referrer
    $logger->debug($request->method() . ' ' . $request->uri() . ' ' . $response->code() . ' ' . $request->userAgent());
    $klein->onError(function ($klein, $error) use ($response, $logger) {
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

$klein->dispatch();
<?php
include_once('src/back/import/import');
include_once('src/back/import/page');
include_once('src/back/labels/HttpStatuses.php');

use Katzgrau\KLogger\Logger as Logger;

class SiteRoutes {
    function __construct(&$klein)
    {

        $logger = new Logger(AU_CONFIG['log.file'], Psr\Log\LogLevel::DEBUG);

        $klein->respond('GET', '/?', function ($request, $response) use ($logger) {
            $page = new MainPage();
            $page->validate($request);
            $response->headers([
                'Content-Type' => 'text/html; charset=utf-8'
            ]);
            return $page->build()->getContent();
        });

        $klein->respond('GET', '/'.UrlParameters::PAGE__CATALOG.'/[:category]?/?', function ($request, $response) use ($logger) {
            $page = new CatalogPage($request);
            $page->validate($request);
            $response->headers([
                'Content-Type' => 'text/html; charset=utf-8'
            ]);
            return $page->build()->getContent();
        });

        $klein->respond('GET', '/'.UrlParameters::PAGE__CONTACTS, function ($request, $response) use ($logger) {
            $page = new ContactsPage($request);
            $page->validate($request);
            $response->headers([
                'Content-Type' => 'text/html; charset=utf-8'
            ]);
            return $page->build()->getContent();
        });

        $klein->respond('GET', '/'.UrlParameters::PAGE__DELIVERY, function ($request, $response) use ($logger) {
            $page = new DeliveryPage($request);
            $page->validate($request);
            $response->headers([
                'Content-Type' => 'text/html; charset=utf-8'
            ]);
            return $page->build()->getContent();
        });
    }
}

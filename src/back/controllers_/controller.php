<?php

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

define('AU_ROOT', __DIR__.'/../../../');
$config = parse_ini_file('config/config.ini');
$GLOBALS['config'] = $config;
define('AU_CONFIG', $config);
include_once __DIR__.'/../import/import.php';
include_once __DIR__.'/../import/pages.php';
include_once __DIR__.'/../labels/HttpStatuses.php';
use Rize\UriTemplate;
use Katzgrau\KLogger\Logger as Logger;

function sendNotFoundPage() {
    header('Content-Type: text/html; charset=utf-8');
    http_response_code(HttpStatuses::NOT_FOUND);
    $page = new NotFoundPage();
    echo $page->getContent();
}

function redirectMain() {
    header('Location: http://' . $_SERVER['HTTP_HOST']);
    exit;
}

function catchInternalError($error) {
    header('Content-Type: application/json');
    $errorModel = new DBErrorType();
    $errorModel->createException($error);
    $logger = new Logger(AU_CONFIG['log.file'], Psr\Log\LogLevel::WARNING);
    $logger->error($error);
    if ($error instanceof ProductNotFoundError) {
        sendNotFoundPage();
        return;
    } else if ($error instanceof BaseError) {
        http_response_code($error->status);
        echo json_encode($error->toJson());
        return;
    }
    http_response_code(HttpStatuses::INTERNAL_SERVER_ERROR);
    $internalError = new InternalError($error);
    echo json_encode($internalError->toJson());
}

function getContent($Page)
{
    if (AU_CONFIG['pages_cache']) {
        $cache = new DBPagesCacheType();
        $existsCache = $cache->getCache($_SERVER["REQUEST_URI"]);
        if ($existsCache != "") {
            $page = $existsCache;
        } else {
            $pageConstructor = new $Page();
            $page = $pageConstructor->getContent();
            $cache->setCache($_SERVER["REQUEST_URI"], $page);
        }
    } else {
        $pageConstructor = new $Page();
        $page = $pageConstructor->getContent();
    }
    return $page;
}
try {

    if (isset($_SERVER['HTTPS'])) {
        header("Location: http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
        exit;
    }

    $uri = new UriTemplate();
    $params = $uri->extract('/{page}/{identifier}', $_SERVER['QUERY_STRING']);
    $pageName = $params['page'];
    $htmlContent = '';

    switch ($pageName) {
        case UrlParameters::PAGE__ADMIN:
            $htmlContent = getContent(AdminPage::class);
            break;
        case UrlParameters::PAGE__MAIN:
            $htmlContent = getContent(MainPage::class);
            break;
        case UrlParameters::PAGE__DELIVERY:
            $htmlContent = getContent(DeliveryPage::class);
            break;
        case UrlParameters::PAGE__CATALOG:
            $htmlContent = (new CatalogPage())->validate()->build()->getContent();
            break;
        case UrlParameters::PAGE__SINGLE_ITEM:
            $htmlContent = getContent(ProductPage::class);
            break;
        case UrlParameters::PAGE__SEARCH:
            $htmlContent = getContent(SearchPage::class);
            break;
        case UrlParameters::PAGE__CONTACTS:
            $htmlContent = getContent(ContactsPage::class);
            break;
        default:
            if ($_SERVER['REQUEST_URI'] == '/google82bab8cc8d403ffc.html') {
                $htmlContent = "google-site-verification: google82bab8cc8d403ffc.html";
            } else if (Utils::isHomeNaked($_SERVER['REQUEST_URI'])) {
                $htmlContent = getContent(MainPage::class);
            }  else {
                redirectMain();
            }
    }
    echo $htmlContent;
} catch (Exception $e) {
    catchInternalError($e);
} catch (Error $e) {
    catchInternalError($e);
}
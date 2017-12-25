<?php
header('Content-Type: text/html; charset=utf-8');
define('AU_ROOT', __DIR__.'/../../../');
$config = parse_ini_file('config/config.ini');
$GLOBALS['config'] = $config;
define('AU_CONFIG', $config);
include_once('src/back/import/import');
include_once('src/back/import/page');
include_once('src/back/labels/HttpStatuses.php');

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
    error_log($error->getMessage());
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
try {

    if (isset($_SERVER['HTTPS'])) {
        header("Location: http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
        exit;
    }

    $pageName = Utils::getFromGET(UrlParameters::PAGE_NAME);
    $page = '';

    function getContent($Page)
    {
        $page = '';
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

    switch ($pageName) {
        case UrlParameters::PAGE__ADMIN:
            $page = getContent(AdminPage::class);
            break;
        case UrlParameters::PAGE__MAIN:
            $page = getContent(MainPage::class);
            break;
        case UrlParameters::PAGE__DELIVERY:
            $page = getContent(DeliveryPage::class);
            break;
        case UrlParameters::PAGE__CATALOG:
            $page = getContent(CatalogPage::class);
            break;
        case UrlParameters::PAGE__SINGLE_ITEM:
            $page = getContent(SingleItemPage::class);
            break;
        case UrlParameters::PAGE__SEARCH:
            $page = getContent(SearchPage::class);
            break;
        case UrlParameters::PAGE__CONTACTS:
            $page = getContent(ContactsPage::class);
            break;
        default:
            if (Utils::isHomeNaked($_SERVER['REQUEST_URI'])) {
                $page = getContent(MainPage::class);
            }  else {
                redirectMain();
            }
    }
    echo $page;
} catch (Exception $e) {
    catchInternalError($e);
} catch (Error $e) {
    catchInternalError($e);
}
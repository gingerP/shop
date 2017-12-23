<?php
define('AU_ROOT', __DIR__.'/../../../');
$config = parse_ini_file('config/config.ini');
$GLOBALS['config'] = $config;
define('AU_CONFIG', $config);
include_once("src/back/import/import");
include_once("src/back/import/page");
function catchInternalError($error) {
    header('Content-Type: application/json');
    $errorModel = new DBErrorType();
    $errorModel->createException($error);
    error_log($error->getMessage());
    if ($error instanceof BaseError) {
        http_response_code($error->status);
        echo json_encode($error->toJson());
        return;
    }
    http_response_code(500);
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
        $cache = new DBPagesCacheType();
        $existsCache = $cache->getCache($_SERVER["REQUEST_URI"]);
        if ($existsCache != "") {
            $page = $existsCache;
        } else {
            $pageConstructor = new $Page();
            $page = $pageConstructor->getContent();
            //$cache->setCache($_SERVER["REQUEST_URI"], $page);
        }
        return $page;
    }

    function redirectMain() {
        header("Location: http://" . $_SERVER["HTTP_HOST"]);
        exit;
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
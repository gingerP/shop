<?php
try {
    $config = parse_ini_file('config/config.ini');
    $GLOBALS['config'] = $config;

    include_once("src/back/import/import");
    include_once("src/back/import/page");
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
            $page = getContent(MainPage::class);
    }

    echo $page;
} catch (Exception $e) {
    http_response_code(500);
    header('Content-type: application/json; charset=UTF-8');
    echo json_encode([
        'http_code' => 500,
        'message' => $e->getMessage()
    ]);
} catch (Error $e) {
    http_response_code(500);
    header('Content-type: application/json; charset=UTF-8');
    echo json_encode([
        'http_code' => 500,
        'message' => $e->getMessage()
    ]);
}
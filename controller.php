<?php
include_once("import");
include_once("page");
if (isset($_SERVER['HTTPS'])) {
    header("Location: http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]);
    exit;
}
$pageName = Utils::getFromGET(UrlParameters::PAGE_NAME);
switch ($pageName) {
    case UrlParameters::PAGE__ADMIN:
        $page = new AdminPage();
        break;
    case UrlParameters::PAGE__MAIN:
        $page = new MainPage();
        break;
    case UrlParameters::PAGE__DELIVERY:
        $page = new DeliveryPage();
        break;
    case UrlParameters::PAGE__CATALOG:
        $page = new CatalogPage();
        break;
    case UrlParameters::PAGE__SINGLE_ITEM:
        $page = new SingleItemPage();
        break;
    case UrlParameters::PAGE__SEARCH:
        $page = new SearchPage();
        break;
    case UrlParameters::PAGE__CONTACTS:
        $page = new ContactsPage();
        break;
    default:
        $page = new MainPage();
}

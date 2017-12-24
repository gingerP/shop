<?php
include_once("src/back/import/import");

class URLBuilder {
    //window.location='/?page_name=search&check_fiz=&check_ur=&search_value=%D1%81&page=2&num=48'
    //window.location='/?page_name=catalog&key=EE&page=2&num=48&high_light_element=EE163&check_fiz=&check_ur='
    private static $catalogLinkRule = array(
        Labels::MAIN_PARAMS =>
            array(UrlParameters::PAGE_NAME
                , UrlParameters::PAGE_NUM
                , UrlParameters::ITEMS_COUNT
                , UrlParameters::VIEW_MODE)
      , Labels::ADDITIONAL_PARAMS =>
            array(array(UrlParameters::KEY)
                , array(UrlParameters::SEARCH_VALUE))
    );

    private static $pathLinkRule = array(
        Labels::MAIN_PARAMS =>
            array(UrlParameters::PAGE_NAME
                , UrlParameters::KEY
                , UrlParameters::PAGE_NUM
                , UrlParameters::ITEMS_COUNT)
    );

    public static function getCatalogLinkForTree($key) {
        //" onclick=\"window.location='?page_name=catalog&key=".$tree->key.Utils::getStoreModeForUrl()."'\"";
        $urlArray = array(
            UrlParameters::PAGE_NAME => UrlParameters::PAGE__CATALOG,
            UrlParameters::KEY => $key
        );
        if (array_key_exists(UrlParameters::VIEW_MODE, $_GET) && array_key_exists(Utils::getFromGET(UrlParameters::VIEW_MODE), Labels::$VIEW_MODE_COMPACT)) {
            $urlArray[UrlParameters::VIEW_MODE] = Utils::getFromGET(UrlParameters::VIEW_MODE);
        }
        if (array_key_exists(UrlParameters::ITEMS_COUNT, $_GET) && in_array(Utils::getFromGET(UrlParameters::ITEMS_COUNT), Labels::$VIEW_MODE_NUMERIC)) {
            $urlArray[UrlParameters::ITEMS_COUNT] = Utils::getFromGET(UrlParameters::ITEMS_COUNT);
        }
        $url = '?'.Utils::buildUrl($urlArray);
        $url = Utils::getUrlWithStoreMode($url);
        return $url;
    }

    public static function getCatalogLinkForSingleItem($pageId, $pageNumber = null, $num = null, $additionalData = []) {
        $urlArray = array(
            UrlParameters::PAGE_NAME => UrlParameters::PAGE__SINGLE_ITEM,
            UrlParameters::PAGE_ID => $pageId
        );

        if ($num != null) {
            $urlArray[UrlParameters::ITEMS_COUNT] = $num;
        }
        if ($pageNumber != null) {
            $urlArray[UrlParameters::PAGE_NUM] = $pageNumber;
        }
        if (array_key_exists(UrlParameters::KEY, $additionalData) && $additionalData[UrlParameters::KEY] != "") {
            $urlArray[UrlParameters::KEY] = $additionalData[UrlParameters::KEY];
        }
        if (array_key_exists(UrlParameters::SEARCH_VALUE, $additionalData) && $additionalData[UrlParameters::SEARCH_VALUE] != "") {
            $urlArray[UrlParameters::SEARCH_VALUE] = $additionalData[UrlParameters::SEARCH_VALUE];
        }
        $url = '?'.Utils::buildUrl($urlArray);
        $url = Utils::getUrlWithStoreMode($url);
        return $url;
    }

    public static function getCatalogLinkPrev($pageNumber, $itemsCount) {
        $urlArray = Utils::createUrlArrayFromCurrentUrl(URLBuilder::$catalogLinkRule);
        $urlArray[UrlParameters::PAGE_NUM] = $pageNumber - 1;
        $urlArray[UrlParameters::ITEMS_COUNT] = $itemsCount;
        $url = '?'.Utils::buildUrl($urlArray);
        $url = Utils::getUrlWithStoreMode($url);
        return $url;
    }

    public static function getCatalogLinkNext($pageNumber, $itemsCount) {
        $urlArray = Utils::createUrlArrayFromCurrentUrl(URLBuilder::$catalogLinkRule);
        $urlArray[UrlParameters::PAGE_NUM] = $pageNumber + 1;
        $urlArray[UrlParameters::ITEMS_COUNT] = $itemsCount;
        $url = '?'.Utils::buildUrl($urlArray);
        $url = Utils::getUrlWithStoreMode($url);
        return $url;
    }

    public static function getCatalogLinkNumeric($pageNumber, $itemsCount) {
        $urlArray = Utils::createUrlArrayFromCurrentUrl(URLBuilder::$catalogLinkRule);
        $urlArray[UrlParameters::PAGE_NUM] = $pageNumber;
        $urlArray[UrlParameters::ITEMS_COUNT] = $itemsCount;
        $url = '?'.Utils::buildUrl($urlArray);
        $url = Utils::getUrlWithStoreMode($url);
        return $url;
    }

    public static function getSingleItemLinkBack($pageNumber, $itemsCount) {
        $urlArray = Utils::createUrlArrayFromCurrentUrl(URLBuilder::$catalogLinkRule);
        $urlArray[UrlParameters::PAGE_NAME] = UrlParameters::PAGE__CATALOG;
        $urlArray[UrlParameters::PAGE_NUM] = $pageNumber;
        $urlArray[UrlParameters::ITEMS_COUNT] = $itemsCount;
        $url = '?'.Utils::buildUrl($urlArray);
        $url = Utils::getUrlWithStoreMode($url);
        return $url;
    }

    public static function getItemLinkForComplexType($backLinkType, $itemId, $pageNum, $itemsPerPage) {
        $url = '';
        if (preg_match('/^([\w]{2}){1}([\d]{0,3}){1}$/', $itemId, $itemInfo, PREG_OFFSET_CAPTURE) == 1) {
            $keyValuePairs = array(UrlParameters::PAGE_NAME => UrlParameters::PAGE__SINGLE_ITEM
                                    , UrlParameters::KEY => $itemInfo[1][0]
                                    , UrlParameters::PAGE_ID => $itemId
                                    , UrlParameters::PAGE_NUM => $pageNum
                                    , UrlParameters::ITEMS_COUNT =>$itemsPerPage);
            $url = '?'.Utils::getUrlWithStoreMode(Utils::buildUrl($keyValuePairs));
        }
        return $url;
    }

    public static function getCatalogLinkForViewMode($num, $mode) {

    }

    public static function getItemLinkForSimpleType($itemId) {
        $url = '';
        if (preg_match('/^([\w]{2}){1}([\d]{0,3}){1}$/', $itemId, $itemInfo, PREG_OFFSET_CAPTURE) == 1) {
            $goods = new DBGoodsType();
            $itemPosition = $goods->getCatalogItemPosition($itemId, DB::TABLE_GOODS___ORDER);
            $page = ceil($itemPosition / Utils::getFromGETWithDefault(UrlParameters::NUM, Constants::DEFAULT_ITEM_COUNT_PER_PAGE));
            $keyValuePairs = array(UrlParameters::PAGE_NAME => UrlParameters::PAGE__CATALOG
                                    , UrlParameters::KEY => $itemInfo[1][0]
                                    , UrlParameters::PAGE_NUM => $page
                                    , UrlParameters::ITEMS_COUNT => Utils::getFromGETWithDefault(UrlParameters::NUM, Constants::DEFAULT_ITEM_COUNT_PER_PAGE)
                                    , UrlParameters::HIGH_LIGHT_ELEMENT => $itemId);
            $url = '?'.Utils::getUrlWithStoreMode(Utils::buildUrl($keyValuePairs));
        }
        return $url;
    }

    public static function getPathLinkSingleItem() {
        return '?'.Utils::getUrlWithStoreMode(Utils::buildUrl(Utils::createUrlArrayFromCurrentUrl(URLBuilder::$pathLinkRule)));
    }

    public static function storeModeFiz($yesNoType) {
        if ($yesNoType == YesNoType::NO) {
            return Utils::removeParameterFromURL(Labels::CHECK_FIZ, Utils::getCurrentURL());
        }
        return Utils::replaceOrAddParameterValueInURL(Labels::CHECK_FIZ, '', Utils::getCurrentURL());
    }

    public static function storeModeUr($yesNoType) {
        if ($yesNoType == YesNoType::NO) {
            return Utils::removeParameterFromURL(Labels::CHECK_UR, Utils::getCurrentURL());
        }
        return Utils::replaceOrAddParameterValueInURL(Labels::CHECK_UR, '', Utils::getCurrentURL());
    }


}
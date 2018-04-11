<?php
include_once AuWebRoot.'/src/back/import/import.php';
use Rize\UriTemplate;

class URLBuilder
{
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

    public static function getCatalogLinkForTree($category)
    {
        $uri = new UriTemplate();
        return $uri->expand(
            '/{pageName}/{category}',
            [
                'pageName' => UrlParameters::PAGE__CATALOG,
                'category' => $category
            ]
        );
    }

    public static function getCatalogLinkForSingleItem($productCode)
    {
        $uri = new UriTemplate();
        return $uri->expand(
            '/{pageName}/{code}',
            [
                'pageName' => UrlParameters::PAGE__PRODUCTS,
                'code' => $productCode
            ]
        );

    }

    public static function getCatalogLinkPrevForSearch($searchValue, $pageNumber, $itemsCount)
    {
        $uri = new UriTemplate();
        return $uri->expand(
            '/{pageName}/{?' . UrlParameters::SEARCH_VALUE . ',' . UrlParameters::PAGE_NUM . ',' . UrlParameters::ITEMS_COUNT . '}',
            [
                'pageName' => UrlParameters::PAGE__CATALOG,
                UrlParameters::SEARCH_VALUE => $searchValue,
                UrlParameters::PAGE_NUM => $pageNumber - 1,
                UrlParameters::ITEMS_COUNT => $itemsCount
            ]
        );
    }

    public static function getCatalogLinkNextForSearch($searchValue, $pageNumber, $itemsCount)
    {
        $uri = new UriTemplate();
        return $uri->expand(
            '/{pageName}/{?' . UrlParameters::SEARCH_VALUE . ',' . UrlParameters::PAGE_NUM . ',' . UrlParameters::ITEMS_COUNT . '}',
            [
                'pageName' => UrlParameters::PAGE__CATALOG,
                UrlParameters::SEARCH_VALUE => $searchValue,
                UrlParameters::PAGE_NUM => $pageNumber + 1,
                UrlParameters::ITEMS_COUNT => $itemsCount
            ]
        );
    }

    public static function getCatalogLinkPrevForCategory($category, $pageNumber, $itemsCount)
    {
        $uri = new UriTemplate();
        return $uri->expand(
            '/{pageName}/{category}{?' . UrlParameters::PAGE_NUM . ',' . UrlParameters::ITEMS_COUNT . '}',
            [
                'pageName' => UrlParameters::PAGE__CATALOG,
                'category' => $category,
                UrlParameters::PAGE_NUM => $pageNumber - 1,
                UrlParameters::ITEMS_COUNT => $itemsCount
            ]
        );
    }

    public static function getCatalogLinkNextForCategory($category, $pageNumber, $itemsCount)
    {
        $uri = new UriTemplate();
        return $uri->expand(
            '/{pageName}/{category}{?' . UrlParameters::PAGE_NUM . ',' . UrlParameters::ITEMS_COUNT . '}',
            [
                'pageName' => UrlParameters::PAGE__CATALOG,
                'category' => $category,
                UrlParameters::PAGE_NUM => $pageNumber + 1,
                UrlParameters::ITEMS_COUNT => $itemsCount
            ]
        );
    }

    public static function getCatalogLinkPrev($pageNumber, $itemsCount)
    {
        $uri = new UriTemplate();
        return $uri->expand(
            '/{pageName}/{?' . UrlParameters::PAGE_NUM . ',' . UrlParameters::ITEMS_COUNT . '}',
            [
                'pageName' => UrlParameters::PAGE__CATALOG,
                UrlParameters::PAGE_NUM => $pageNumber - 1,
                UrlParameters::ITEMS_COUNT => $itemsCount
            ]
        );
    }

    public static function getCatalogLinkNext($pageNumber, $itemsCount)
    {
        $uri = new UriTemplate();
        return $uri->expand(
            '/{pageName}{?' . UrlParameters::PAGE_NUM . ',' . UrlParameters::ITEMS_COUNT . '}',
            [
                'pageName' => UrlParameters::PAGE__CATALOG,
                UrlParameters::PAGE_NUM => $pageNumber + 1,
                UrlParameters::ITEMS_COUNT => $itemsCount
            ]
        );
    }

    public static function getCatalogLinkForCategory($category, $pageNumber, $itemsCount)
    {
        $uri = new UriTemplate();
        return $uri->expand(
            '/{pageName}/{category}{?' . UrlParameters::PAGE_NUM . ',' . UrlParameters::ITEMS_COUNT . '}',
            [
                'pageName' => UrlParameters::PAGE__CATALOG,
                'category' => $category,
                UrlParameters::PAGE_NUM => $pageNumber,
                UrlParameters::ITEMS_COUNT => $itemsCount
            ]
        );
    }

    public static function getCatalogLink($pageNumber = 0, $itemsCount = 0)
    {
        if ($pageNumber === 0) {
            return '/' . UrlParameters::PAGE__CATALOG;
        }
        $uri = new UriTemplate();
        return $uri->expand(
            '/{pageName}/{?' . UrlParameters::PAGE_NUM . ',' . UrlParameters::ITEMS_COUNT . '}',
            [
                'pageName' => UrlParameters::PAGE__CATALOG,
                UrlParameters::PAGE_NUM => $pageNumber,
                UrlParameters::ITEMS_COUNT => $itemsCount
            ]
        );
    }

    public static function getCatalogLinkForSearch($searchValue, $pageNumber, $itemsCount)
    {
        $uri = new UriTemplate();
        return $uri->expand(
            '/{pageName}/{?' . UrlParameters::PAGE_NUM . ',' . UrlParameters::ITEMS_COUNT . '}',
            [
                'pageName' => UrlParameters::PAGE__CATALOG,
                UrlParameters::SEARCH_VALUE => $searchValue,
                UrlParameters::PAGE_NUM => $pageNumber,
                UrlParameters::ITEMS_COUNT => $itemsCount
            ]
        );
    }

    public static function getSingleItemLinkBack($pageNumber, $itemsCount)
    {
        $urlArray = Utils::createUrlArrayFromCurrentUrl(URLBuilder::$catalogLinkRule);
        $urlArray[UrlParameters::PAGE_NAME] = UrlParameters::PAGE__CATALOG;
        $urlArray[UrlParameters::PAGE_NUM] = $pageNumber;
        $urlArray[UrlParameters::ITEMS_COUNT] = $itemsCount;
        $url = '?' . Utils::buildUrl($urlArray);
        $url = Utils::getUrlWithStoreMode($url);
        return $url;
    }

    public static function getItemLinkForComplexType($backLinkType, $itemId, $pageNum, $itemsPerPage)
    {
        $url = '';
        if (preg_match('/^([\w]{2}){1}([\d]{0,3}){1}$/', $itemId, $itemInfo, PREG_OFFSET_CAPTURE) == 1) {
            $keyValuePairs = array(UrlParameters::PAGE_NAME => UrlParameters::PAGE__SINGLE_ITEM
            , UrlParameters::KEY => $itemInfo[1][0]
            , UrlParameters::PAGE_ID => $itemId
            , UrlParameters::PAGE_NUM => $pageNum
            , UrlParameters::ITEMS_COUNT => $itemsPerPage);
            $url = '?' . Utils::getUrlWithStoreMode(Utils::buildUrl($keyValuePairs));
        }
        return $url;
    }

    public static function getCatalogLinkForViewMode($num, $mode)
    {

    }

    public static function getItemLinkForSimpleType($itemId)
    {
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
            $url = '?' . Utils::getUrlWithStoreMode(Utils::buildUrl($keyValuePairs));
        }
        return $url;
    }

    public static function getPathLinkSingleItem()
    {
        return '?' . Utils::getUrlWithStoreMode(Utils::buildUrl(Utils::createUrlArrayFromCurrentUrl(URLBuilder::$pathLinkRule)));
    }

    public static function storeModeFiz($yesNoType)
    {
        if ($yesNoType == YesNoType::NO) {
            return Utils::removeParameterFromURL(Labels::CHECK_FIZ, Utils::getCurrentURL());
        }
        return Utils::replaceOrAddParameterValueInURL(Labels::CHECK_FIZ, '', Utils::getCurrentURL());
    }

    public static function storeModeUr($yesNoType)
    {
        if ($yesNoType == YesNoType::NO) {
            return Utils::removeParameterFromURL(Labels::CHECK_UR, Utils::getCurrentURL());
        }
        return Utils::replaceOrAddParameterValueInURL(Labels::CHECK_UR, '', Utils::getCurrentURL());
    }


}
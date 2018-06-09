<?php

function extractProductId($product)
{
    return $product[DB::TABLE_GOODS__ID];
}

class SearchService
{

    private static $mustache;

    public static function searchAsHtml($searchValue, $page = 0, $limit = 10, $includeNav = true, $includeContacts = true, $shouldNormalize = true)
    {
        $responseData = self::search($searchValue, $page = 0, $limit = 10, $includeNav = true, $includeContacts = true, $shouldNormalize = true);
        $renderEngine = self::getRenderEngine();
        $template = $renderEngine->loadTemplate('search-response.mustache');
        return $template->render($responseData);
    }

    public static function search($searchValue, $page = 0, $limit = 10, $includeNav = true, $includeContacts = true, $shouldNormalize = true)
    {
        $connection = (new DBConnection())->init();

        $link = $connection->getLink();
        $escapedValue = mysqli_real_escape_string($link, $searchValue);
        $result = [];
        $isEmpty = true;

        if ($includeNav == true) {
            $navKeysType = (new DBNavKeyType())->setConnection($connection);
            $navs = $navKeysType->extractDataFromResponse($navKeysType->executeRequestLikeArrayWithLimit(
                [DB::TABLE_NAV_KEY__VALUE],
                [$escapedValue],
                DB::TABLE_NAV_KEY__VALUE, 'asc', 0, 20
            ));
            $result['navs'] = $shouldNormalize == true ? self::normalizeNavs($navs) : $navs;
            $isEmpty = $isEmpty && count($result['navs']) == 0;
        }

        if ($includeContacts == true) {
            $addressType = (new DBAddressType())->setConnection($connection);
            $contacts = $addressType->extractDataFromResponse($addressType->executeRequestLikeArrayWithLimit(
                [DB::TABLE_ADDRESS__ADDRESS, DB::TABLE_ADDRESS__EMAIL, DB::TABLE_ADDRESS__TITLE],
                [$escapedValue, $escapedValue, $escapedValue],
                DB::TABLE_ADDRESS__TITLE, 'asc', 0, 20
            ));
            $result['contacts'] = $shouldNormalize == true ? self::normalizeContacts($contacts) : $contacts;
            $isEmpty = $isEmpty && count($result['contacts']) == 0;
        }

        $Products = (new DBGoodsType())->setConnection($connection);
        $products = $Products->searchByNameDescription($escapedValue, $page * $limit, $limit);
        $result['products'] = $shouldNormalize == true ? self::normalizeProducts($products['list']) : $products['list'];
        $isEmpty = $isEmpty && count($result['products']) == 0;
        $result['isEmpty'] = $isEmpty;
        $result['productsTotalCount'] = intval($products['totalCount']);
        return $result;
    }

    private static function normalizeProducts($products)
    {
        $normalized = [];
        if ($products) {
            $Preferences = new DBPreferencesType();
            $catalogPath = $Preferences->getPreference(SettingsNames::CATALOG_PATH)[DB::TABLE_PREFERENCES__VALUE];
            while ($product = array_shift($products)) {
                $code = $product[DB::TABLE_GOODS__KEY_ITEM];
                $imagesCodes = json_decode($product[DB::TABLE_GOODS__IMAGES]);
                $imagesSmall = ProductsUtils::normalizeImagesFromCodes($imagesCodes, $code, Constants::SMALL_IMAGE, $catalogPath);
                array_push($normalized, [
                    'name' => $product[DB::TABLE_GOODS__NAME],
                    'url' => URLBuilder::getCatalogLinkForSingleItem($product[DB::TABLE_GOODS__KEY_ITEM]),
                    'icon' => count($imagesSmall) ? '/' . $imagesSmall[0] : ''
                ]);
            }
        }
        return $normalized;
    }

    private static function normalizeContacts($contacts)
    {
        $normalized = [];
        if ($contacts) {
            while ($contact = array_shift($contacts)) {
                array_push($normalized, [
                    'name' => $contact[DB::TABLE_ADDRESS__TITLE] . ', ' . $contact[DB::TABLE_ADDRESS__ADDRESS],
                    'url' => '?page_name=contacts'
                ]);
            }
        }
        return $normalized;
    }

    private static function normalizeNavs($navs)
    {
        $normalized = [];
        if ($navs) {
            while ($nav = array_shift($navs)) {
                $code = $nav[DB::TABLE_NAV_KEY__KEY_ITEM];
                array_push($normalized, [
                    'name' => $nav[DB::TABLE_NAV_KEY__VALUE],
                    'url' => URLBuilder::getCatalogLinkForTree($code)
                ]);
            }
        }
        return $normalized;
    }

    public static function getRenderEngine()
    {
        if (is_null(self::$mustache)) {
            self::$mustache = new Mustache_Engine([
                'loader' => new Mustache_Loader_FilesystemLoader(realpath(__DIR__)),
                'pragmas' => [Mustache_Engine::PRAGMA_FILTERS]
            ]);
        }
        return self::$mustache;
    }

}
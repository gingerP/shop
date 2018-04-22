<?php

function extractProductId($product) {
    return $product[DB::TABLE_GOODS__ID];
}

class SearchService
{

    public static function searchAsHtml() {

    }

    public static function search($searchValue, $page = 0, $limit = 10, $includeNav = true, $includeContacts = true, $shouldNormalize = true)
    {
        $connection = (new DBConnection())->init();
        $addressType = (new DBAddressType())->setConnection($connection);
        $goodsType = (new DBGoodsType())->setConnection($connection);
        $navKeysType = (new DBNavKeyType())->setConnection($connection);

        $link = $connection->getLink();
        $escapedValue = mysqli_real_escape_string($link, $searchValue);
        $result = [];
        if ($includeNav == true) {
            $navs = $navKeysType->extractDataFromResponse($navKeysType->executeRequestLikeArrayWithLimit(
                [DB::TABLE_NAV_KEY__VALUE],
                [$escapedValue],
                DB::TABLE_NAV_KEY__VALUE, 'asc', 0, 20
            ));
            $result['navs'] = $shouldNormalize == true ? self::normalizeNavs($navs) : $navs;
        }
        if ($includeContacts == true) {
            $contacts = $addressType->extractDataFromResponse($addressType->executeRequestLikeArrayWithLimit(
                [DB::TABLE_ADDRESS__ADDRESS, DB::TABLE_ADDRESS__EMAIL, DB::TABLE_ADDRESS__TITLE],
                [$escapedValue, $escapedValue, $escapedValue],
                DB::TABLE_ADDRESS__TITLE, 'asc', 0, 20
            ));
            $result['contacts'] = $shouldNormalize == true ? self::normalizeContacts($contacts) : $contacts;
        }
        $products = $goodsType->extractDataFromResponse($goodsType->executeRequestLikeArrayWithLimit(
            [DB::TABLE_GOODS__NAME], [$escapedValue],
            DB::TABLE_GOODS__NAME, 'asc', 0, 200
        ));
        $productsIds = array_map('extractProductId', $products);
        $goodsType = (new DBGoodsType())->setConnection($connection);
        $products = array_merge(
            $products,
            $goodsType->extractDataFromResponse($goodsType->searchByCriteriaExcludingIds(
                [DB::TABLE_GOODS__DESCRIPTION], [$escapedValue], $productsIds,
                DB::TABLE_GOODS__NAME, 'asc', 0, 200
            ))
        );
        $result['products'] = $shouldNormalize == true ? self::normalizeProducts($products) : $products;

        return $result;
    }

    private static function normalizeProducts($products)
    {
        $normalized = [];
        if ($products) {
            $Preferences = new DBPreferencesType();
            $catalogPath = $Preferences->getPreference(Constants::CATALOG_PATH)[DB::TABLE_PREFERENCES__VALUE];
            while ($product = array_shift($products)) {
                $code = $product[DB::TABLE_GOODS__KEY_ITEM];
                $imagesCodes = json_decode($product[DB::TABLE_GOODS__IMAGES]);
                $imagesSmall = ProductsUtils::normalizeImagesFromCodes($imagesCodes, $code, Constants::SMALL_IMAGE, $catalogPath);
                array_push($normalized, [
                    'name' => $product[DB::TABLE_GOODS__NAME],
                    'url' => URLBuilder::getCatalogLinkForSingleItem($product[DB::TABLE_GOODS__KEY_ITEM]),
                    'icon' => $imagesSmall[0]
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

}
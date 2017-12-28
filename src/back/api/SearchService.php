<?php

/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 10/31/14
 * Time: 12:19 AM
 */

function extractProductId($product) {
    return $product[DB::TABLE_GOODS__ID];
}

class SearchService
{

    public static function search($searchValue, $page = 0, $limit = 10)
    {
        $connection = (new DBConnection())->init();
        $addressType = (new DBAddressType())->setConnection($connection);
        $goodsType = (new DBGoodsType())->setConnection($connection);
        $navKeysType = (new DBNavKeyType())->setConnection($connection);

        $link = $connection->getLink();
        $escapedValue = mysqli_real_escape_string($link, $searchValue);
        $result = [];
        $navs = $navKeysType->extractDataFromResponse($navKeysType->executeRequestLikeArrayWithLimit(
            [DB::TABLE_NAV_KEY__VALUE],
            [$escapedValue],
            DB::TABLE_NAV_KEY__VALUE, 'asc', 0, 20
        ));
        $contacts = $addressType->extractDataFromResponse($addressType->executeRequestLikeArrayWithLimit(
            [DB::TABLE_ADDRESS__ADDRESS, DB::TABLE_ADDRESS__EMAIL, DB::TABLE_ADDRESS__TITLE],
            [$escapedValue, $escapedValue, $escapedValue],
            DB::TABLE_ADDRESS__TITLE, 'asc', 0, 20
        ));
        $result['navs'] = self::normalizeNavs($navs);
        $result['contacts'] = self::normalizeContacts($contacts);
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
        $result['products'] = self::normalizeProducts($products);

        return $result;
    }

    private function normalizeProducts($products)
    {
        $normalized = [];
        if ($products) {
            while ($product = array_shift($products)) {
                $code = $product[DB::TABLE_GOODS__KEY_ITEM];
                $iconPath = FileUtils::getFirstFileInDirectoryByPrefixByDescription(Constants::DEFAULT_ROOT_CATALOG_PATH . DIRECTORY_SEPARATOR . $code . DIRECTORY_SEPARATOR, Constants::SMALL_IMAGE, 'jpg');
                array_push($normalized, [
                    'name' => $product[DB::TABLE_GOODS__NAME],
                    'url' => URLBuilder::getCatalogLinkForSingleItem($product[DB::TABLE_GOODS__KEY_ITEM], null, null, []),
                    'icon' => Utils::normalizeAbsoluteImagePath($iconPath),
                ]);
            }
        }
        return $normalized;
    }

    private function normalizeContacts($contacts)
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

    private function normalizeNavs($navs)
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
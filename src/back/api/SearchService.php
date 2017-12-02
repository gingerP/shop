<?php

/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 10/31/14
 * Time: 12:19 AM
 */
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
        if ($page == 0) {
            $navs = $navKeysType->executeRequestLikeArrayWithLimit(
                [DB::TABLE_NAV_KEY__VALUE],
                [$escapedValue],
                DB::TABLE_NAV_KEY__VALUE, 'asc', 0, 20
            );
            $contacts = $addressType->executeRequestLikeArrayWithLimit(
                [DB::TABLE_ADDRESS__ADDRESS, DB::TABLE_ADDRESS__EMAIL, DB::TABLE_ADDRESS__TITLE],
                [$escapedValue, $escapedValue, $escapedValue],
                DB::TABLE_ADDRESS__ADDRESS, 'asc', 0, 20
            );
            $result['navs'] = self::normalizeNavs($navs);
            $result['contacts'] = self::normalizeContacts($contacts);
        }
        $products = $goodsType->executeRequestLikeArrayWithLimit(
            [DB::TABLE_GOODS__DESCRIPTION, DB::TABLE_GOODS__NAME],
            [$escapedValue, $escapedValue],
            DB::TABLE_GOODS__NAME, 'asc', $page, $limit
        );
        $result['products'] = self::normalizeProducts($products);

        return $result;
    }

    private function normalizeProducts($products)
    {
        $normalized = [];
        if ($products) {
        while ($product = mysqli_fetch_array($products)) {
            $code = $product[DB::TABLE_GOODS__KEY_ITEM];
            $fileUrl = FileUtils::getFirstFileInDirectoryByPrefixByDescription(Constants::DEFAULT_ROOT_CATALOG_PATH . DIRECTORY_SEPARATOR . $code . DIRECTORY_SEPARATOR, Constants::SMALL_IMAGE, 'jpg');
            array_push($normalized, [
                'name' => $product[DB::TABLE_GOODS__NAME],
                'url' => URLBuilder::getCatalogLinkForSingleItem($product[DB::TABLE_GOODS__KEY_ITEM], null, null, []),
                'icon' => $fileUrl,
            ]);
        }
        }
        return $normalized;
    }

    private function normalizeContacts($contacts)
    {
        $normalized = [];
        if ($contacts) {
        while ($contact = mysqli_fetch_array($contacts)) {
            array_push($normalized, [
                'name' => $contact[DB::TABLE_ADDRESS__TITLE].', '.$contact[DB::TABLE_ADDRESS__ADDRESS],
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
        while ($nav = mysqli_fetch_array($navs)) {
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
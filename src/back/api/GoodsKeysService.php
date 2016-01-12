<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 3/17/15
 * Time: 12:10 AM
 */

include_once('import');
include_once('db');

class GoodsKeysService {

    public static function getList() {
        $dbGoodsKeys = new DBNavKeyType();
        $dbGoodsKeys->getList();
        $ret = [];
        $resKeys = [
            DB::TABLE_NAV_KEY__ID,
            DB::TABLE_NAV_KEY__VALUE,
            DB::TABLE_NAV_KEY__KEY_ITEM,
            DB::TABLE_NAV_KEY__PARENT_KEY,
            DB::TABLE_NAV_KEY__HOME_VIEW,
        ];
        $response = $dbGoodsKeys->getResponse();
        while ($row = mysql_fetch_array($response)) {
            $item = [];
            foreach ($resKeys as $key) {
                $item[$key] = $row[$key];
            }
            array_push($ret, $item);
        }
        return $ret;
    }

} 
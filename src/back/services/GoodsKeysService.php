<?php
include_once AuWebRoot.'/src/back/import/import.php';
include_once AuWebRoot.'/src/back/import/db.php';

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
        while ($row = mysqli_fetch_array($response)) {
            $item = [];
            foreach ($resKeys as $key) {
                $item[$key] = $row[$key];
            }
            array_push($ret, $item);
        }
        return $ret;
    }

} 
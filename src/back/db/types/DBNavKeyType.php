<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vinni
 * Date: 11.09.13
 * Time: 3:15
 * To change this template use File | Settings | File Templates.
 */
include_once("db");

class DBNavKeyType extends DBType {

    public function DBNavKeyType() {
        $this->DBType();
    }

    public  function getNameByKey($key) {
        $this->executeRequest(DB::TABLE_NAV_KEY__KEY_ITEM, $key, DB::TABLE_ORDER, DB::ASC);
        if ($this->responseSize != 0) {
            $row = mysql_fetch_array($this->response);
            return $row[DB::TABLE_NAV_KEY__VALUE];
        }
        return '';
    }

    public function getLeafs() {
        $individual = array_key_exists(UrlParameters::CHECK_FIZ, $_GET) ? "g.individual='YES'" : '';
        $person = array_key_exists(UrlParameters::CHECK_UR, $_GET) ? "g.person='YES'": '';
        $store = '';
        if ($individual != '' && $person != '') {
            $store = " (".$individual." or ".$person.") and ";
        } elseif ($individual == '' && $person != '') {
            $store = $person." and ";
        } elseif ($individual != '' && $person == '') {
            $store = $individual." and ";
        }
        $sqlCommand = "SELECT DISTINCT nk.* FROM nav_key nk, goods g  WHERE ".$store." nk.key_item=SUBSTRING(g.key_item, 1, 2)";
        $navKeys = new DBNavKeyType();
        return $navKeys->execute($sqlCommand);
    }

    protected function getTable() {
        return DB::TABLE_NAV_KEY___NAME;
    }

    protected function getTableName() {
        return DB::TABLE_NAV_KEY___NAME;
    }

    protected function getIndexColumn() {
        return DB::TABLE_NAV_KEY__ID;
    }

    protected function getOrder() {
        return DB::TABLE_NAV_KEY___ORDER;
    }
}
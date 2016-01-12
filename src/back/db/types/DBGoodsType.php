<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vinni
 * Date: 11.09.13
 * Time: 3:15
 * To change this template use File | Settings | File Templates.
 */
include_once("import");
include_once("db");

class DBGoodsType extends DBType{

    protected $tableName = DB::TABLE_GOODS___NAME;

    public function DBGoodsType() {
        $this->DBType();
        return $this;
    }

    protected function getOrder() {
        return DB::TABLE_GOODS___ORDER;
    }

    protected function getIndexColumn() {
        return DB::TABLE_GOODS__ID;
    }

    public function getGoodsSearchCount(&$whereParamKeyArray, &$whereValueArray) {
        $countRequest = "SELECT COUNT(t.".DB::TABLE_ID.") FROM ".$this->getTable()." AS t";
        for($i = 0; $i < count($whereParamKeyArray); $i++) {
            if ($whereParamKeyArray[$i] != '' && $whereValueArray[$i] != '') {
                if ($i == 0) {
                    $countRequest = $countRequest." WHERE";
                } elseif ($i != count($whereParamKeyArray)) {
                    $countRequest = $countRequest." OR";
                }
                $countRequest = $countRequest." t.".$whereParamKeyArray[$i]." REGEXP '".$whereValueArray[$i]."'";
            }
        }
        $row = mysql_fetch_row($this->execute($countRequest));
        $this->totalCount = $row[0];
        Log::db("DBConnection.getGoodsSearchCount: ".$countRequest." TOTALCOUNT: ".$this->totalCount);
    }

    public function getGoodsKeyCount($keys) {
        $pattern = "^(".implode("|", $keys)."){1}[0-9]{0,3}$";
        $countRequest = "SELECT COUNT(t.".DB::TABLE_ID.") FROM ".$this->getTable()." AS t WHERE t.".DB::TABLE_GOODS__KEY_ITEM." REGEXP '".$pattern."'";
        $row = mysql_fetch_row($this->execute($countRequest));
        $this->totalCount = $row[0];
        Log::db("DBConnection.getGoodsKeyCount: ".$countRequest." TOTALCOUNT: ".$this->totalCount);
    }

    protected function getTable() {
        if (!array_key_exists(Labels::CHECK_UR, $_GET) && !array_key_exists(Labels::CHECK_FIZ, $_GET)) {
            return "(SELECT * FROM ".DB::TABLE_GOODS___NAME.")";
            //return "(SELECT * FROM ".DB::TABLE_GOODS___NAME." WHERE ".DB::TABLE_GOODS__INDIVIDUAL."='YES' OR ".DB::TABLE_GOODS__PERSON."='YES')";
        } else {

            $individual = array_key_exists(UrlParameters::CHECK_FIZ, $_GET) ? DB::TABLE_GOODS__INDIVIDUAL."='".YesNoType::YES."'" : '';
            $person = array_key_exists(UrlParameters::CHECK_UR, $_GET) ? DB::TABLE_GOODS__PERSON."='".YesNoType::YES."'": '';
            $store = "(SELECT * FROM ".$this->tableName." WHERE ";
            if ($individual != '' && $person != '') {
                $store = $store.$individual." or ".$person.")";
            } elseif ($individual == '' && $person != '') {
                $store = $store.$person.")";
            } elseif ($individual != '' && $person == '') {
                $store = $store.$individual.")";
            }
            return $store;
        }
    }

    public function getRandomRowByKeys($keys, $randomItemsCount) {
        if ($randomItemsCount < 1) {
            $randomItemsCount = 1;
        }
        $regexp = "'^(";
        for($index = 0; $index < count($keys); $index++) {
            $regexp.= $index > 0 ? "|" : "";
            $regexp.=$keys[$index];
        }
        $regexp.="){1}[0-9]{1,3}$'";
        $this->request = "SELECT * FROM ".DB::TABLE_GOODS___NAME
                                ." WHERE ".DB::TABLE_GOODS__KEY_ITEM." REGEXP ".$regexp." ORDER BY RAND() LIMIT ".$randomItemsCount;
        $this->execute($this->request);
        $this->totalCount = mysql_num_rows($this->response);
        Log::db("DBConnection.getRandomRowByKeys: ".$this->request." TOTALCOUNT: ".$this->totalCount);
        return $this->getResponse();
    }

    public function getRandomRowByKeysWithDefault($keys, $randomItemsCount, $defKeys, $defRandomItemsCount) {
        $ret = $this->getRandomRowByKeys($keys, $randomItemsCount);
        if ($this->getTotalCount() == 0) {
            return $this->getRandomRowByKeys($defKeys, $defRandomItemsCount);
        }
        return $ret;
    }

    public function getCatalogItemPosition($itemId, $sort_column) {
        $tableGoods_NAME = DB::TABLE_GOODS___NAME;
        $tableGoods_keyItem = DB::TABLE_GOODS__KEY_ITEM;
        if (preg_match('/^([\w]{2}){1}([\d]{0,3}){1}$/', $itemId, $itemInfo, PREG_OFFSET_CAPTURE) == 1) {
            $keyItemParent = $itemInfo[1][0];
            $this->request = "SELECT COUNT(*) FROM $tableGoods_NAME WHERE $tableGoods_keyItem REGEXP '^($keyItemParent){1}[0-9]{1,3}$' AND $sort_column <= (SELECT $sort_column FROM $tableGoods_NAME WHERE $tableGoods_keyItem='$itemId')";
            $row = mysql_fetch_row($this->execute($this->request));
            $this->totalCount = $row[0];
            Log::db("goods DBConnection.getCatalogItemPosition: $this->request | totalcount: $this->totalCount");
        }
        return $this->totalCount;
    }

    public function getCode($id) {
        $row = $this->get($id);
        return $row[DB::TABLE_GOODS__KEY_ITEM];
    }

    protected function getTableName() {
        return $this->tableName;
    }
}
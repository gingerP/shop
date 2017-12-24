<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vinni
 * Date: 11.09.13
 * Time: 3:15
 * To change this template use File | Settings | File Templates.
 */
include_once("src/back/import/import");
include_once("src/back/import/db");

class DBGoodsType extends DBType{

    protected $tableName = DB::TABLE_GOODS___NAME;

    public function __construct() {
        parent::__construct();
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
        $row = mysqli_fetch_row($this->execute($countRequest));
        $this->totalCount = $row[0];
        Log::db("DBConnection.getGoodsSearchCount: ".$countRequest." TOTALCOUNT: ".$this->totalCount);
    }

    public function getGoodsKeyCount($keys) {
        $pattern = "^(".implode("|", $keys)."){1}[0-9]{0,3}$";
        $countRequest = "SELECT COUNT(t.".DB::TABLE_ID.") FROM ".$this->getTable()." AS t WHERE t.".DB::TABLE_GOODS__KEY_ITEM." REGEXP '".$pattern."'";
        $row = mysqli_fetch_row($this->execute($countRequest));
        $this->totalCount = $row[0];
        Log::db("DBConnection.getGoodsKeyCount: ".$countRequest." TOTALCOUNT: ".$this->totalCount);
    }

    protected function getTable() {
        return "(SELECT * FROM ".DB::TABLE_GOODS___NAME.")";
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
        $this->totalCount = mysqli_num_rows($this->response);
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
            $row = mysqli_fetch_row($this->execute($this->request));
            $this->totalCount = $row[0];
            Log::db("goods DBConnection.getCatalogItemPosition: $this->request | totalcount: $this->totalCount");
        }
        return $this->totalCount;
    }

    public function getAdminSortedForCommon($limitBegin, $limitEnd) {
        $this->request = "SELECT * FROM ".$this->getTableName()." AS t ";
        $this->request = $this->request." LEFT JOIN ".DB::TABLE_USER_ORDER___NAME." uo ON t.".DB::TABLE_GOODS__ID." = uo.".DB::TABLE_USER_ORDER__GOOD_ID." ";
        $this->request = $this->request." ORDER BY uo.".DB::TABLE_USER_ORDER__GOOD_INDEX." ASC, t.".DB::TABLE_GOODS__ID." ASC ";
        $this->request = $this->request." LIMIT ".$limitBegin.",".$limitEnd;
        $this->execute($this->request);
        Log::db("DBConnection.getUserSortedForCommon REQUEST: ".$this->request);
        return $this->response;
    }

    public function getUserSortedForMenu($goodKeys, $limitBegin, $limitEnd) {
        $regExp = "^(".implode('|', $goodKeys)."){1}";
        $this->request = "SELECT * FROM ".$this->getTableName()." AS t";
        $this->request = $this->request." LEFT JOIN ".DB::TABLE_USER_ORDER___NAME." uo ON t.".DB::TABLE_GOODS__ID." = uo.".DB::TABLE_USER_ORDER__GOOD_ID." ";
        $this->request = $this->request." WHERE LOWER(t.".DB::TABLE_GOODS__CATEGORY.") REGEXP '$regExp'";
        $this->request = $this->request." ORDER BY uo.".DB::TABLE_USER_ORDER__GOOD_INDEX." ASC, t.".DB::TABLE_GOODS__ID." ASC ";
        $this->request = $this->request." LIMIT ".$limitBegin.",".$limitEnd;
        $this->execute($this->request);
        Log::db("DBConnection.getUserSortedForMenu REQUEST: ".$this->request." RESPONSE_COUNT: ".$this->responseSize);
        return $this->response;
    }

    public function getUserSortedForSearch($keyMas, $valueMas, $limitBegin, $limitEnd) {
        $this->request = "SELECT * FROM ".$this->getTableName()." AS t ";
        $this->request = $this->request." left join ".DB::TABLE_USER_ORDER___NAME." uo on t.".DB::TABLE_GOODS__ID." = uo.".DB::TABLE_USER_ORDER__GOOD_ID." ";
        for($i = 0; $i < count($keyMas); $i++) {
            if ($keyMas[$i] != '' && $valueMas[$i] != '') {
                if ($i == 0) {
                    $this->request = $this->request." WHERE";
                } elseif ($i != count($keyMas)) {
                    $this->request = $this->request." OR";
                }
                $this->request = $this->request." LOWER(t.".$keyMas[$i].") REGEXP '".$valueMas[$i]."'";
            }
        }
        $this->request = $this->request." ORDER BY uo.".DB::TABLE_USER_ORDER__GOOD_INDEX." ASC, t.".DB::TABLE_GOODS__ID." ASC ";
        $this->request = $this->request." LIMIT ".$limitBegin.",".$limitEnd;
        $this->responseSize = mysqli_num_rows($this->execute($this->request));
        Log::db("DBConnection.executeRequestRegExpArrayWithLimit REQUEST: ".$this->request);
        return $this->response;
    }

    public function getCode($id) {
        $row = $this->get($id);
        return $row[DB::TABLE_GOODS__KEY_ITEM];
    }

    public function getByCode($keyItem) {
        $response = $this->executeRequest(DB::TABLE_GOODS__KEY_ITEM, $keyItem, DB::TABLE_GOODS___ORDER, DB::ASC);
        $row = mysqli_fetch_array($response);
        if ($row) {
            return $row;
        }
        return null;
    }

    public function getCategoriesCount() {
        $result = [];
        $this->request = "SELECT count(*) count, category FROM ".$this->getTableName()." GROUP BY ".DB::TABLE_GOODS__CATEGORY.";";
        $this->execute($this->request);
        while($row = mysqli_fetch_array($this->response)) {
            $result[$row['category']] = $row['count'];
        }
        return $result;
    }

    protected function getTableName() {
        return $this->tableName;
    }
}
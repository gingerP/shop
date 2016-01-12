<?php
include_once("db");
include_once("page");


class CatalogLoader {

    public $dataTotalCount;
    public $dataCount;
    public $data;

    public function getItemsMainData($pageNumber, $num) {
        $goods = new DBGoodsType();
        $limitBegin = ($pageNumber - 1) * $num;
        $limitEnd = $num;
        $goods->executeRequestWithLimit('', '', DB::TABLE_GOODS___ORDER, DB::ASC, $limitBegin, $limitEnd);
        $this->data = $goods->getResponse();
        $this->dataCount = $goods->getResponseSize();
        $goods->executeTotalCount();
        $this->dataTotalCount = $goods->getTotalCount();
    }

    public function getItemsMenuData($pageNumber, $num, $key) {
        $goods = new DBGoodsType();
        $navKeys = new DBNavKeyType();
        $treeUtils = new TreeUtils();
        $navKeys->executeRequest('', '', DB::TABLE_ORDER);
        $tree = $treeUtils->buildTreeByLeafs();
        $keys = $treeUtils->getTreeLeafesForKey($tree, $key);
        $str = implode('|', $keys);
        $goods->getGoodsKeyCount($keys);
        $this->dataTotalCount = $goods->getTotalCount();
        $limitBegin = ($pageNumber - 1) * $num;
        $limitEnd = $num;
        $goods->executeRequestRegExpWithLimit(DB::TABLE_NAV_KEY__KEY_ITEM, "^(".$str."){1}", DB::TABLE_GOODS___ORDER, DB::ASC, $limitBegin, $limitEnd);
        $this->data = $goods->getResponse();
        $this->dataCount = $goods->getResponseSize();
    }

    public function getItemSearchData($pageNumber, $num, $valueToSearch) {
        $goods = new DBGoodsType();
        $keyMas = array();
        $valueMas = array();
        array_push($keyMas, DB::TABLE_GOODS__NAME);
        array_push($keyMas, DB::TABLE_GOODS__DESCRIPTION);
        array_push($valueMas, "(".mb_convert_case($valueToSearch, MB_CASE_LOWER, "utf-8").")+");
        array_push($valueMas, "(".mb_convert_case($valueToSearch, MB_CASE_LOWER, "utf-8").")+");
        $limitBegin = ($pageNumber - 1) * $num;
        $limitNum = $num;
        $goods->executeRequestRegExpArrayWithLimit($keyMas, $valueMas, DB::TABLE_GOODS___ORDER, DB::ASC, $limitBegin, $limitNum);
        $this->data = $goods->getResponse();
        $this->dataCount = $goods->getResponseSize();
        $goods->getGoodsSearchCount($keyMas, $valueMas);
        $this->dataTotalCount = $goods->getTotalCount();
    }

}
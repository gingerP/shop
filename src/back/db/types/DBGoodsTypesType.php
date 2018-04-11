<?php

include_once AuWebRoot.'/src/back/import/import.php';
include_once AuWebRoot.'/src/back/import/db.php';

class DBGoodsTypesType extends DBType{

    protected $tableName = DB::TABLE_GOODS_TYPES___NAME;

    public function __construct() {
        parent::__construct();
        return $this;
    }

    protected function getTable() {
        return $this->tableName;
    }

    protected function getTableName() {
        return $this->tableName;
    }

    protected function getIndexColumn() {
        return DB::TABLE_GOODS_TYPES__ID;
    }

    protected function getOrder() {
        return DB::TABLE_GOODS_TYPES___ORDER;
    }

} 
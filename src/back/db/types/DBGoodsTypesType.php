<?php
include_once("import");
include_once("db");

class DBGoodsTypesType extends DBType{

    protected $tableName = DB::TABLE_GOODS_TYPES___NAME;

    public function DBGoodsTypesType() {
        $this->DBType();
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
<?php

include_once AuWebRoot.'/src/back/import/db.php';

class DBBookletsType extends DBType {
    protected $tableName = DB::TABLE_BOOKLET___NAME;

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
        return DB::TABLE_BOOKLET__ID;
    }

    protected function getOrder() {
        return DB::TABLE_BOOKLET___ORDER;
    }

}
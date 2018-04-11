<?php

include_once AuWebRoot.'/src/back/import/db.php';

class DBNewsType extends DBType {
    protected $tableName = DB::TABLE_NEWS___NAME;

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
        return DB::TABLE_NEWS__ID;
    }

    protected function getOrder() {
        return DB::TABLE_NEWS___ORDER;
    }
}
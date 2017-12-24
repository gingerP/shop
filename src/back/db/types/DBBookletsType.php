<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 11/6/14
 * Time: 1:08 AM
 */

include_once("src/back/import/db");

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
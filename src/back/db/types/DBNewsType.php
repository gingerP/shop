<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 11/6/14
 * Time: 1:08 AM
 */

include_once("src/back/import/db");

class DBNewsType extends DBType {
    protected $tableName = DB::TABLE_NEWS___NAME;

    public function DBNewsType() {
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
        return DB::TABLE_NEWS__ID;
    }

    protected function getOrder() {
        return DB::TABLE_NEWS___ORDER;
    }
}
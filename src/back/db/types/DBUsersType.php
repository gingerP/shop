<?php

include_once AuWebRoot.'/src/back/import/db.php';

class DBUsersType extends DBType {
    protected $tableName = DB::TABLE_USERS___NAME;

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
        return DB::TABLE_USERS__ID;
    }

    protected function getOrder() {
        return DB::TABLE_USERS___ORDER;
    }

    public function getUserForName($name) {
        $this->executeRequestWithLimit(DB::TABLE_USERS__NAME, $name, DB::TABLE_USERS___ORDER, DB::ASC, 0, 1);
        $row = mysqli_fetch_array($this->response);
        return $row;
    }

}
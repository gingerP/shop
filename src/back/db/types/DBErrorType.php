<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 11/6/14
 * Time: 1:08 AM
 */

include_once("src/back/import/db");

class DBErrorType extends DBType {
    protected $tableName = DB::TABLE_ERRORS___NAME;

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
        return DB::TABLE_ERRORS__ID;
    }

    protected function getOrder() {
        return DB::TABLE_ERRORS___ORDER;
    }

    public function createException($error) {
        if (is_array($error)) {
            $this->update(null,
                [
                    DB::TABLE_ERRORS__NAME => $error['name'],
                    DB::TABLE_ERRORS__STACK => $error['stack'],
                    DB::TABLE_ERRORS__DATE => $error['date'],
                    DB::TABLE_ERRORS__MESSAGE => $error['message']
                ]
            );
            return;
        }
        $this->update(null,
            [
                DB::TABLE_ERRORS__NAME => get_class($error),
                DB::TABLE_ERRORS__STACK => $error->getTraceAsString(),
                DB::TABLE_ERRORS__DATE => date("Y-m-d H:i:s"),
                DB::TABLE_ERRORS__MESSAGE => $error->getMessage()
            ]
        );
    }

}
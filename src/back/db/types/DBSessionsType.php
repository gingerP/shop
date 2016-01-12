<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 11/6/14
 * Time: 1:08 AM
 */

include_once("db");

class DBSessionsType extends DBType {
    protected $tableName = DB::TABLE_SESSIONS___NAME;

    public function DBSessionsType() {
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
        return DB::TABLE_SESSIONS__ID;
    }

    protected function getOrder() {
        return DB::TABLE_SESSIONS___ORDER;
    }

    public function getSession($name) {
        $this->executeRequestWithLimit(DB::TABLE_SESSIONS__NAME, $name, DB::TABLE_SESSIONS___ORDER, DB::ASC, 0, 1);
        while ($row = mysql_fetch_array($this->response)) {
            return $row;
        }
        return [];
    }

    public function getSessionForUser($user) {
        $dbUserType = new DBUsersType();
        $userRow = $dbUserType->getUserForName($user);
        if ($userRow != null) {
            $userId = $userRow[DB::TABLE_USERS__ID];
            $this->executeRequestWithLimit(DB::TABLE_SESSIONS__USER_ID, $userId, DB::TABLE_SESSIONS___ORDER, DB::ASC, 0, 1);
            while ($row = mysql_fetch_array($this->response)) {
                return $row;
            }
        }
        return null;
    }

} 
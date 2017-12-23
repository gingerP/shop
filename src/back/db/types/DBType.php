<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vinni
 * Date: 11.09.13
 * Time: 3:30
 * To change this template use File | Settings | File Templates.
 */
include_once("src/back/import/import");

abstract class DBType {

    protected $connection = null;
    protected $totalCount = 0;
    protected $request = "";
    protected $response = null;
    protected $responseSize = 0;

    protected function DBType() {
    }

    public function executeRequestWithLimit($whereParamKey, $whereParamValue, $order, $orderRule, $limitBegin, $limitEnd) {
        $this->request = "SELECT t.* FROM ".$this->getTable()." AS t ";
        if ($whereParamKey != '') $this->request = $this->request." WHERE t.".$whereParamKey."='".$whereParamValue."'";
        if ($order != '') $this->request = $this->request." ORDER BY t.".$order." ".$orderRule;
        $this->request = $this->request." LIMIT ".$limitBegin.",".$limitEnd;
        $this->execute($this->request);
        Log::db("DBConnection.executeRequestWithLimit REQUEST: ".$this->request);
        return $this->response;
    }

    public function getResponseSize() {    
        return $this->responseSize;
    }

    public function executeRequest($whereParamKey, $whereParamValue, $order, $orderRule = 'asc') {
        return $this->executeRequestWithLimit($whereParamKey, $whereParamValue, $order, $orderRule, 0, 5000);
    }

    public function executeRequestRegExpArrayNoLimit(&$whereParamKeyArray, &$whereValueArray, $order, $orderRule) {
        $this->executeRequestRegExpArrayWithLimit($whereParamKeyArray, $whereValueArray, $order, $orderRule, 0, 5000);
        return $this->response;
    }

    public function executeRequestRegExpArrayWithLimit($whereParamKeyArray, $whereValueArray, $order, $orderRule, $limitBegin, $limitNum) {
        $this->request = "SELECT t.* FROM ".$this->getTable()." AS t ";
        for($i = 0; $i < count($whereParamKeyArray); $i++) {
            if ($whereParamKeyArray[$i] != '' && $whereValueArray[$i] != '') {
                if ($i == 0) {
                    $this->request = $this->request." WHERE";
                } elseif ($i != count($whereParamKeyArray)) {
                    $this->request = $this->request." OR";
                }
                $this->request = $this->request." LOWER(t.".$whereParamKeyArray[$i].") REGEXP '".$whereValueArray[$i]."'";
            }
        }
        if ($order != '') $this->request = $this->request." ORDER BY t.".$order." ".$orderRule;
        $this->request = $this->request." LIMIT ".$limitBegin.",".$limitNum;
        $this->execute($this->request);
        Log::db("DBConnection.executeRequestRegExpArrayWithLimit REQUEST: ".$this->request);
        return $this->response;
    }

    public function executeRequestLikeArrayWithLimit($whereParamKeyArray, $whereValueArray, $order, $orderRule, $limitBegin, $limitNum) {
        $this->request = "SELECT t.* FROM ".$this->getTable()." AS t ";
        for($i = 0; $i < count($whereParamKeyArray); $i++) {
            if ($whereParamKeyArray[$i] != '' && $whereValueArray[$i] != '') {
                if ($i == 0) {
                    $this->request = $this->request." WHERE";
                } elseif ($i != count($whereParamKeyArray)) {
                    $this->request = $this->request." OR";
                }
                $this->request = $this->request." LOWER(t.".$whereParamKeyArray[$i].") LIKE '%".$whereValueArray[$i]."%'";
            }
        }
        if ($order != '') $this->request = $this->request." ORDER BY t.".$order." ".$orderRule;
        $this->request = $this->request." LIMIT ".$limitBegin.",".$limitNum;
        $this->execute($this->request);
        Log::db("DBConnection.executeRequestRegExpArrayWithLimit REQUEST: ".$this->request);
        return $this->response;
    }

    public function executeTotalCount() {
        $countRequest = "SELECT COUNT(t.".DB::TABLE_ID.") FROM ".$this->getTable()." AS t";
        $row = mysqli_fetch_row($this->execute($countRequest));
        $this->totalCount = $row[0];
        Log::db("DBConnection.getTotalCount: ".$countRequest." TOTALCOUNT: ".$this->totalCount);
        return $this->response;
    }

    public function executeRequestRegExpNoLimit($whereParamKey, $regexp, $order, $orderRule) {
        return $this->executeRequestRegExpWithLimit($whereParamKey, $regexp, $order, $orderRule,  0, 5000);
    }

    public function executeRequestRegExpWithLimit($whereParamKey, $regexp, $order, $orderRule, $limitBegin, $limitEnd) {
        $this->request = "SELECT t.* FROM ".$this->getTable()." AS t";
        if ($whereParamKey != '') $this->request = $this->request." WHERE LOWER(t.".$whereParamKey.") REGEXP '".$regexp."'";
        if ($order != '') $this->request = $this->request." ORDER BY t.".$order." ".$orderRule;
        $this->request = $this->request." LIMIT ".$limitBegin.",".$limitEnd;
        $this->execute($this->request);
        Log::db("DBConnection.executeRequestRegExpWithLimit REQUEST: ".$this->request." RESPONSE_COUNT: ".$this->responseSize);
        return $this->response;
    }

    public function get($id) {
        $this->request = "SELECT * FROM ".$this->getTable()." AS t WHERE t.".$this->getIndexColumn()."=".$id;
        $this->execute($this->request);
        Log::db("DBConnection.get REQUEST: ".$this->request." RESPONSE_COUNT: ".$this->responseSize);
        if ($this->response) {
            return mysqli_fetch_array($this->response);
        }
        return null;
    }

    public function getList($mapping = null) {
        $fields = "*";
        if ($mapping != null && count($mapping) > 0) {
            $fields = implode(",", array_values($mapping));
        }
        $this->request = "SELECT ".$fields." FROM ".$this->getTable();
        $this->execute($this->request);
        Log::db("DBConnection.getList REQUEST: ".$this->request." RESPONSE_COUNT: ".$this->responseSize);
        return $this->response;
    }

    public function getListActive($mapping = null) {
        $fields = "*";
        if ($mapping != null && count($mapping) > 0) {
            $fields = implode(",", array_values($mapping));
        }
        $this->request = "SELECT ".$fields." FROM ".$this->getTable()." WHERE ".DB::TABLE_ACTIVE."=true ORDER BY ".$this->getOrder();

        try {
            $this->execute($this->request);
            Log::db("DBConnection.getList REQUEST: ".$this->request." RESPONSE_COUNT: ".$this->responseSize);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        return $this->response;
    }

    public function update($id, $valuesAssoc) {
        if ($id != null && $id != '') {
            $this->request = "UPDATE ".$this->getTableName()." SET ";
            $requestParams = [];
            foreach($valuesAssoc as $key => $value) {
                if (strlen(trim($key)) > 0) {
                    array_push($requestParams, $key."='".$value."'");
                }
            }
            $this->request .= join($requestParams, ",")." WHERE ".$this->getIndexColumn()."='".$id."';";
            $this->execute($this->request);
            Log::db("DBConnection.update REQUEST: ".$this->request);
            return $id;
        } else {
            $this->initConnection();
            $columns = [];
            $values = [];
            $link = $this->connection->getLink();
            foreach($valuesAssoc as $key => $value) {
                array_push($columns, mysqli_real_escape_string($link, $key));
                array_push($values, mysqli_real_escape_string($link, $value));
            }
            $this->request = "INSERT INTO ".$this->getTableName()."(".join($columns, ",").") VALUES ('".join($values, "','")."');";
            $this->execute($this->request);
            $id_ = $this->connection->insertId;
            Log::db("DBConnection.update REQUEST: ".$this->request." RESPONSE: ".$id_);
            return $id_;
        }
        return -1;
    }

    public function delete($id) {
        $rowsCount = 0;
        if ($id != null && $id != '') {
            $this->request = "DELETE FROM ".$this->getTableName()." WHERE ".$this->getIndexColumn()."=".$id;
            $this->execute($this->request);
            $rowsCount = $this->connection->affectedRows;
        }
        return $rowsCount;
    }

    public function clear() {
        $this->request = "TRUNCATE TABLE ".$this->getTableName();
        $this->execute($this->request);
        Log::db("DBConnection.update REQUEST: ".$this->request);
        return true;
    }

    public function initConnection() {
        if ($this->connection == null) {
            $this->connection = new DBConnection();
            $this->connection = $this->connection->init();
            return $this->connection;
        }
        return $this->connection;
    }

    protected function execute($sqlCommand) {
        $this->connection = $this->connection == null ? new DBConnection() : $this->connection;
        $this->response = $this->connection->execute($sqlCommand);
        if (is_bool($this->response)) {
            $this->responseSize = 0;
        } else {
            $this->responseSize = mysqli_num_rows($this->response);
        }
        return $this->response;
    }

    public function getResponse() {
        return $this->response;
    }

    public function getTotalCount() {
        return $this->totalCount;
    }

    public function getRequest() {
        return $this->request;
    }

    public function setRequest($request) {
        $this->request = $request;
    }

    public function removeAll() {
        $this->request = "DELETE * FROM ".$this->getTableName();
        $this->execute($this->request);
        $rowsCount = $this->connection->affectedRows;
        Log::db("DBConnection.removeAll ".$this->getTableName()." REQUEST: ".$this->request." RESPONSE: ".$rowsCount);
        return $rowsCount;
    }

    protected function getTable() {
    }

    protected function getTableName() {
    }

    protected function getIndexColumn() {
    }

    protected function getOrder() {
    }

    public function extractDataFromResponse($response, $mapping) {
        $result = [];
        while ($row = mysqli_fetch_array($response)) {
            array_push($result, Utils::extractObject($row, $mapping));
        }
        return $result;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function setConnection($connection) {
        $this->connection = $connection;
        return $this;
    }
}
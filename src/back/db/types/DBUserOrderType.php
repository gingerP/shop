<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 10/31/14
 * Time: 1:05 AM
 */
include_once("src/back/import/import");

class DBUserOrderType extends DBType{

    protected $tableName = DB::TABLE_USER_ORDER___NAME;

    public function DBUserOrderType() {
        $this->DBType();
        return $this;
    }

    public function saveOrder($orderData) {
        if (count($orderData) > 0 && $this->clear()) {
            $orderData = $this->correctOrderData($orderData);
            $this->request = $this->createOrderRequest($orderData);
            $this->execute($this->request);
            Log::db("DBConnection.update REQUEST: ".$this->request);
            return true;
        }
        return false;
    }

    private function createOrderRequest($orderData) {
        $count = count($orderData);
        if ($count > 0) {
            $request = "INSERT INTO " . $this->getTableName() . "(" . DB::TABLE_USER_ORDER__GOOD_ID . ", " . DB::TABLE_USER_ORDER__GOOD_INDEX . ") VALUES ";
            for ($i = 0; $i < $count; $i++) {
                $request = $request . "(" . intval($orderData[$i][DB::TABLE_USER_ORDER__GOOD_ID]) . ", " . intval($orderData[$i][DB::TABLE_USER_ORDER__GOOD_INDEX]) . "),";
            }
            if ($count > 0) {
                $request = rtrim($request, ",");
            }
            return $request.";";
        }
        return "";
    }

    private function correctOrderData($orderData) {
        Log::info("correctOrderData BEGIN");
        for($i = count($orderData) - 1; $i >= 0; $i--) {
            if (!is_numeric($orderData[$i][DB::TABLE_USER_ORDER__GOOD_ID]) || !is_numeric($orderData[$i][DB::TABLE_USER_ORDER__GOOD_INDEX])) {
                array_splice($orderData, $i, 1);
                Log::info("correctOrderData DATA ".$orderData[$i][DB::TABLE_USER_ORDER__GOOD_ID]);
            }
        }
        Log::info("correctOrderData FINISH");
        return $orderData;
    }

    protected function getTable() {
        return $this->tableName;
    }

    protected function getTableName() {
        return $this->tableName;
    }

    protected function getIndexColumn() {
        return DB::TABLE_USER_ORDER__ID;
    }

    protected function getOrder() {
        return DB::TABLE_USER_ORDER___ORDER;
    }
}
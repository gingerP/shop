<?php

include_once AuWebRoot.'/src/back/import/import.php';

class DBUserOrderType extends DBType{

    protected $tableName = DB::TABLE_USER_ORDER___NAME;

    public function __construct() {
        parent::__construct();
        return $this;
    }

    public function saveOrder($orderData) {
        if (count($orderData) > 0 && $this->clear()) {
            $orderData = $this->correctOrderData($orderData);
            $this->request = $this->createOrderRequest($orderData);
            $this->execute($this->request);
            $this->logger->debug("DBConnection.update REQUEST: ".$this->request);
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
        for($i = count($orderData) - 1; $i >= 0; $i--) {
            $productId = $orderData[$i][DB::TABLE_USER_ORDER__GOOD_ID];
            $productIndex = $orderData[$i][DB::TABLE_USER_ORDER__GOOD_INDEX];
            if (!is_numeric($productId) || !is_numeric($productIndex)) {
                array_splice($orderData, $i, 1);
            }
        }
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
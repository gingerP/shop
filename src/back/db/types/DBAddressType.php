<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 10/31/14
 * Time: 1:05 AM
 */

class DBAddressType extends DBType{

    protected $tableName = DB::TABLE_ADDRESS___NAME;

    public function DBAddressType() {
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
        return DB::TABLE_ADDRESS__ID;
    }

    protected function getOrder() {
        return DB::TABLE_ADDRESS___ORDER;
    }

    public function getActiveAddresses() {
        $ret = array();
        $this->getListActive();
        $response = $this->getResponse();
        while ($row = mysql_fetch_array($response)) {
            $address = new Address();
            $address->id = $row[DB::TABLE_ADDRESS__ID];
            $address->description = $row[DB::TABLE_ADDRESS__DESCRIPTION];
            $address->map = $row[DB::TABLE_ADDRESS__MAP];
            $address->prices = explode("|", $row[DB::TABLE_ADDRESS__PRICES]);
            $numbers = explode("|", $row[DB::TABLE_ADDRESS__MOBILE_NUMBERS]);
            for ($numberIndex = 0; $numberIndex < count($numbers); $numberIndex++) {
                $key = $numbers[$numberIndex];
                $value = "";
                $numberIndex++;
                if ($numberIndex < count($numbers)) {
                    $value = $numbers[$numberIndex];
                }
                $address->mobileNumbers[$key] = $value;
            }
            array_push($ret, $address);
        }
        return $ret;
    }
}
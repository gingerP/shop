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
        while ($row = mysqli_fetch_array($response)) {
            $address = new Address();
            $address->id = $row[DB::TABLE_ADDRESS__ID];
            $address->description = $row[DB::TABLE_ADDRESS__DESCRIPTION];
            $address->map = $row[DB::TABLE_ADDRESS__MAP];
            if (strlen(trim($row[DB::TABLE_ADDRESS__PRICES])) > 0) {
                $address->prices = explode("|", $row[DB::TABLE_ADDRESS__PRICES]);
            } else {
                $address->prices = [];
            }

            if (strlen(trim($row[DB::TABLE_ADDRESS__MOBILE_NUMBERS])) > 0) {
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
            } else {
                $address->mobileNumbers = [];
            }

            $address->title = $row[DB::TABLE_ADDRESS__TITLE];
            $address->address = $row[DB::TABLE_ADDRESS__ADDRESS];
            $address->weekend = json_decode($row[DB::TABLE_ADDRESS__WEEKEND]);
            $address->working_hours = json_decode($row[DB::TABLE_ADDRESS__WORKING_HOURS]);
            $address->email = $row[DB::TABLE_ADDRESS__EMAIL];
            array_push($ret, $address);
        }
        return $ret;
    }
}
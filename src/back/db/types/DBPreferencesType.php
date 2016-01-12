<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 11/6/14
 * Time: 1:08 AM
 */

class DBPreferencesType extends DBType {
    protected $tableName = DB::TABLE_PREFERENCES___NAME;

    private static $cachedValues = [];
    private static $cachedValuesAges = [];
    private static $prefTimeout = 10;

    public function DBPreferencesType() {
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
        return DB::TABLE_PREFERENCES__ID;
    }

    protected function getOrder() {
        return DB::TABLE_PREFERENCES___ORDER;
    }

    public function getPreference($key) {
        $this->executeRequestWithLimit(DB::TABLE_PREFERENCES__KEY, $key, DB::TABLE_PREFERENCES__KEY, DB::ASC, 0, 1);
        while ($row = mysql_fetch_array($this->response)) {
            return $row;
        }
        return [];
    }

    public static function getPreferenceS($key) {
        if (!array_key_exists($key, self::$cachedValues) || self::$cachedValues[$key] == '' || self::$cachedValues[$key] == null || self::$cachedValuesAges[$key] + self::$prefTimeout <= time()) {
            $pref = new DBPreferencesType();
            self::$cachedValues[$key] = $pref->getPreference($key)[DB::TABLE_PREFERENCES__VALUE];
            self::$cachedValuesAges[$key] = time();
        }
        return self::$cachedValues[$key];
    }
} 
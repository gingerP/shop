<?php

class DBPreferencesType extends DBType {
    protected $tableName = DB::TABLE_PREFERENCES___NAME;

    private static $cachedValues = [];
    private static $cachedValuesAges = [];
    private static $prefTimeout = 10;

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
        return DB::TABLE_PREFERENCES__ID;
    }

    protected function getOrder() {
        return DB::TABLE_PREFERENCES___ORDER;
    }

    public function getPreference($key) {
        $this->executeRequestWithLimit(DB::TABLE_PREFERENCES__KEY, $key, DB::TABLE_PREFERENCES__KEY, DB::ASC, 0, 1);
        while ($row = mysqli_fetch_array($this->response)) {
            return $row;
        }
        return [];
    }

    public static function getPreferenceValue($key, $defaultValue = '') {
        if (self::isValueNotCached($key)) {
            $pref = new DBPreferencesType();
            $preference = $pref->getPreference($key);
            if (count($preference) == 0) {
                return $defaultValue;
            }
            self::$cachedValues[$key] = self::getCastValue($preference);
            self::$cachedValuesAges[$key] = time();
        }
        return self::$cachedValues[$key];
    }

    private static function getCastValue($preference) {
        $rawValue = $preference[DB::TABLE_PREFERENCES__VALUE];
        switch($preference[DB::TABLE_PREFERENCES__VALUE_TYPE]) {
            case 'string': return $rawValue;
            case 'json': return json_decode($rawValue, true);
        }
    }

    private static function isValueNotCached($key) {
        return !array_key_exists($key, self::$cachedValues)
            || self::$cachedValues[$key] == ''
            || self::$cachedValues[$key] == null
            || self::$cachedValuesAges[$key] + self::$prefTimeout <= time();
    }
}
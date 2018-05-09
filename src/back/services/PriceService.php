<?php
include_once AuWebRoot.'/src/back/import/import.php';
include_once AuWebRoot.'/src/back/import/db.php';

class PriceService {

    const PRICE_ADD = "add";
    const PRICE_DEL = "del";
    const PRICE_RENAME = "rename";
    private static $operationsOrder = [self::PRICE_DEL, self::PRICE_RENAME, self::PRICE_ADD];

    public static function getPrices() {
        $ret = [];
        $pref = new DBPreferencesType();
        $pricesDir = $pref->getPreference(Constants::PRICE_DIRECTORY)[DB::TABLE_PREFERENCES__VALUE];
        $prices = FileUtils::getFilesByDescription($pricesDir, 'xls');
        $pricesX = FileUtils::getFilesByDescription($pricesDir, 'xlsx');
        $files = array_merge($prices, $pricesX);
        sort($files);
        for ($fileIndex = 0; $fileIndex < count($files); $fileIndex++) {
            $arr = [];
            $arr['name'] = end(explode(DIRECTORY_SEPARATOR, $files[$fileIndex]));
            $arr['path'] = $files[$fileIndex];
            $arr['size'] = round((filesize($files[$fileIndex]) / 1024), 2)." kb";
            $arr['modification_time'] = date("Y/m/d h:i:s", filemtime($files[$fileIndex]));
            array_push($ret, $arr);
        }
        return $ret;
    }

    public static function deletePrice($fileName) {
        $pref = new DBPreferencesType();
        $pricesDir = $pref->getPreference(Constants::PRICE_DIRECTORY)[DB::TABLE_PREFERENCES__VALUE];
        return unlink($pricesDir.DIRECTORY_SEPARATOR.$fileName);
    }

/*
 * prices example
    {
        "06rmlrn3vdkj4i":{
            "id":"_new:0",
            "name":"Лакокрасочные материалы 2014.xlsx",
            "new_name":null,
            "file":null,
            "action":null,
            "size":"30.64 kb",
            "modification_time":"2015-01-16 01:08:12",
            "_isNew":true,
            "path":"prices/Лакокрасочные материалы 2014.xlsx"
        },
        ...
    }
*/

    public static function updatePrices($data) {
        $result = [];
        if (count($data) > 0) {
            $dbPreference = new DBPreferencesType();
            $priceDirectory = $dbPreference->getPreference(Constants::PRICE_DIRECTORY)[DB::TABLE_PREFERENCES__VALUE];
            for($orderIndex = 0; $orderIndex < count(self::$operationsOrder); $orderIndex++) {
                $currentAction = self::$operationsOrder[$orderIndex];
                foreach ($data as $key => $value) {
                    if ($value['action'] == $currentAction) {
                        $operationRes = false;
                        switch ($currentAction) {
                            case self::PRICE_DEL:
                                $operationRes = unlink(FileUtils::buildPath($priceDirectory, $value['name']));
                                break;
                            case self::PRICE_RENAME:
                                $operationRes = rename(FileUtils::buildPath($priceDirectory, $value['name']), FileUtils::buildPath($priceDirectory, $value['new_name']));
                                break;
                            case self::PRICE_ADD:
                                $operationRes = FileUtils::createFileBase64($value['file'], FileUtils::buildPath($priceDirectory, $value['name']));
                                break;
                        }
                        $result[$key] = $operationRes;
                    }
                }
            }
        }
        return $result;
    }
} 
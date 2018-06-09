<?php

define('AuWebRoot', __DIR__ . '/../../../');
require AuWebRoot . '/vendor/autoload.php';
$config = parse_ini_file(AuWebRoot . '/config/config.ini');
define('AU_CONFIG', $config);
$GLOBALS['config'] = $config;
include_once AuWebRoot . '/src/back/import/db.php';

$Products = new DBGoodsType();
$products = $Products->extractDataFromResponse($Products->getList());
foreach ($products as $product) {
    $json = json_decode($product[DB::TABLE_GOODS__DESCRIPTION], true, 512, JSON_UNESCAPED_UNICODE);
    if (is_null($json)) {
        $descriptionJson = [];
        $list = explode('|', $product[DB::TABLE_GOODS__DESCRIPTION]);
        foreach ($list as $keyValue) {
            $keyValuePair = explode('=', $keyValue);
            if (count($keyValuePair) > 0) {
                $key = $keyValuePair[0];
                $values = count($keyValuePair) > 1 && strlen($keyValuePair[1]) > 0 ? explode(';', $keyValuePair[1]) : [];
                $descriptionJson[$key] = $values;
            }
        }
        $Products->update(
            $product[DB::TABLE_GOODS__ID],
            [
                DB::TABLE_GOODS__DESCRIPTION => json_encode($descriptionJson, JSON_UNESCAPED_UNICODE)
            ]
        );
        echo 'Description for product ' . $product[DB::TABLE_GOODS__ID] . ' successfully converted to json.'."\n";
    } else {
        echo 'Description for product ' . $product[DB::TABLE_GOODS__ID] . ' successfully already json.'."\n";
    }
}
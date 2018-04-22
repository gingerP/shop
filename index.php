<?php
require __DIR__ . '/vendor/autoload.php';
use Katzgrau\KLogger\Logger as Logger;

$config = parse_ini_file('config/config.ini');
$localization = parse_ini_file('config/messages.ini');
$GLOBALS['config'] = $config;
define('AuWebRoot', __DIR__);
define('AU_CONFIG', $config);
include_once AuWebRoot.'/src/back/utils/LocalizationHelpers.php';
$localization = LocalizationHelpers::parseAssocArrayDeeply($localization);
define('Localization', $localization);

include_once AuWebRoot.'/src/back/routes/index.php';
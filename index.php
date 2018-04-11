<?php
require __DIR__ . '/vendor/autoload.php';

$config = parse_ini_file('config/config.ini');
$localization = parse_ini_file('config/messages.ini');
$GLOBALS['config'] = $config;
define('AuWebRoot', __DIR__);
define('AU_CONFIG', $config);
define('Localization', $localization);

include_once AuWebRoot.'/src/back/routes/index.php';
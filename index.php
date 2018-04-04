<?php
require __DIR__ . '/vendor/autoload.php';

$config = parse_ini_file('config/config.ini');
$localization = parse_ini_file('config/messages.ini');
$GLOBALS['config'] = $config;
define('AU_CONFIG', $config);
define('Localization', $localization);

include_once 'src/back/routes/index.php';
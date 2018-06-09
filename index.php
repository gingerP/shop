<?php

define('AuWebRoot', __DIR__);
define('AuApiRoot', __DIR__ . '/src/back/api/');

require __DIR__ . '/vendor/autoload.php';

require AuWebRoot . '/src/back/import/import.php';
include_once AuWebRoot.'/src/back/import/db.php';
include_once AuWebRoot.'/src/back/import/services.php';

require AuWebRoot . '/src/back/core/Server.php';

Server::getInstance([
    'routes' => AuWebRoot . '/src/back/routes/index.php'
]);

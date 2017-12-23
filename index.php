<?php

$controller = $_GET['controller'];
switch($controller) {
    case 'site':
        include_once('src/back/controllers/controller.php');
        break;
    case 'api':
        include_once('src/back/controllers/controller-api.php');
        break;
    case 'admin':
        include_once('src/back/controllers/controller-admin.php');
        break;
    default:
        include_once('src/back/controllers/controller.php');
        break;
}
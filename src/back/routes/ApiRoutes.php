<?php
include_once AuWebRoot.'/src/back/import/db.php';
include_once AuWebRoot.'/src/back/import/services.php';
include_once AuWebRoot.'/src/back/api/email/ApiEmail.php';
include_once AuWebRoot.'/src/back/api/contacts/ApiContacts.php';
include_once AuWebRoot.'/src/back/api/products/ApiProducts.php';
use Katzgrau\KLogger\Logger as Logger;

class ApiRoutes {
    function __construct(&$klein)
    {
        $logger = new Logger(AU_CONFIG['log.file'], Psr\Log\LogLevel::DEBUG);

        new ApiEmail($klein, $logger);
        new ApiContacts($klein, $logger);
        new ApiProducts($klein, $logger);

    }
}
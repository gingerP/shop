<?php

include_once AuWebRoot.'/src/back/labels/HttpStatuses.php';
include_once AuWebRoot.'/src/back/errors/BaseError.php';

class NotSecuredConnectionError extends BaseError
{
    public function __construct($message = 'Not Secured Connection Error') {
        parent::__construct($message, HttpStatuses::FORBIDDEN);
    }

}
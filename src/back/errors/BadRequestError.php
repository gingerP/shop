<?php

include_once AuWebRoot.'/src/back/labels/HttpStatuses.php';
include_once AuWebRoot.'/src/back/errors/BaseError.php';

class BadRequestError extends BaseError
{
    public function __construct($message) {
        parent::__construct($message, HttpStatuses::BAD_REQUEST);
    }

}
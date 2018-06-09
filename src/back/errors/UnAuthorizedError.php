<?php

include_once AuWebRoot.'/src/back/labels/HttpStatuses.php';
include_once AuWebRoot.'/src/back/errors/BaseError.php';

class UnAuthorizedError extends BaseError
{
    public function __construct($message = 'Unauthorized Error') {
        parent::__construct($message, HttpStatuses::UNAUTHORIZED);
    }

}
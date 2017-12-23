<?php

include_once('src/back/labels/HttpStatuses.php');
include_once('src/back/errors/BaseError.php');

class InternalError extends BaseError
{
    public function __construct($message) {
        parent::__construct($message, HttpStatuses::INTERNAL_SERVER_ERROR);
    }

}
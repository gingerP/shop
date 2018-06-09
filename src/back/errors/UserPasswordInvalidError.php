<?php

include_once AuWebRoot.'/src/back/labels/HttpStatuses.php';
include_once AuWebRoot.'/src/back/errors/BaseError.php';

class UserPasswordInvalidError extends BaseError
{
    public function __construct($message = "User or Password are invalid.") {
        parent::__construct($message, HttpStatuses::UNAUTHORIZED);
    }

}
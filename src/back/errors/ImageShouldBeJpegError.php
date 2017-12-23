<?php

include_once('src/back/labels/HttpStatuses.php');
include_once('src/back/errors/BaseError.php');

class ImageShouldBeJpegError extends BaseError
{
    public function __construct($message = null) {
        parent::__construct(
            is_null($message) ?  $GLOBALS['AU_MESSAGES']['image_should_be_jpeg'] : $message,
            HttpStatuses::BAD_REQUEST
        );
    }

}
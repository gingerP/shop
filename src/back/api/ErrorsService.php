<?php
include_once('src/back/import/db');
include_once('src/back/import/import');

class ErrorsService
{

    public static function saveError($message, $stack, $pageUrl)
    {
        $Errors = new DBErrorType();
        $Errors->saveExceptionFromParams('FrontEndError', $message." ($pageUrl)", $stack);
        return [];
    }

}
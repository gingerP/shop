<?php
include_once AuWebRoot.'/src/back/import/db.php';
include_once AuWebRoot.'/src/back/import/import.php';

class ErrorsService
{

    public static function saveError($message, $stack, $pageUrl)
    {
        $Errors = new DBErrorType();
        $Errors->saveExceptionFromParams('FrontEndError', $message." ($pageUrl)", $stack);
        return [];
    }

}
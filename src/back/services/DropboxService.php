<?php

include_once AuWebRoot.'/src/back/import/db.php';
include_once AuWebRoot.'/src/back/import/import.php';
include_once AuWebRoot.'/src/back/import/errors.php';

class DropboxService
{
    public static function downloadFile($filePath)
    {
        $accessToken = DBPreferencesType::getPreferenceValue(Constants::DROPBOX_ACCESS_TOKEN);
        $params = json_encode(['path' => $filePath]);
        $opts = array('http' =>
            array(
                'method' => 'POST',
                'header' => [
                    "Authorization: Bearer $accessToken",
                    "Dropbox-API-Arg: $params"
                ]
            )
        );
        $context = stream_context_create($opts);
        return file_get_contents('https://content.dropboxapi.com/2/files/download', false, $context);
    }
}
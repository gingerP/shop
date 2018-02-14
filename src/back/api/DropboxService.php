<?php

include_once('src/back/import/db');
include_once('src/back/import/import');
include_once('src/back/import/errors');

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
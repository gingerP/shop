define([], function () {
    'use strict';

    function AuDropboxFileDownloader(dropboxClient) {
        this._client = dropboxClient;
    }

    AuDropboxFileDownloader.prototype.download = function download(filePath, fileType) {
        var self = this;
        self._client.sharingCreateSharedLinkWithSettings({
            path: filePath,
            settings: {
                requested_visibility: 'public',
                expires: ''
            }
        });

    };

    return AuDropboxFileDownloader;
});

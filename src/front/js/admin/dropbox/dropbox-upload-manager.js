'use strict';
define([
    'dropbox/dropbox-file-upload'
], function (AuDropboxFileUpload) {

    function AuDropboxUploadManager(client, statusListener) {
        this._client = client;
        this._files = [];
        this._filesUploads = [];
        this._statusListener = statusListener;
    }

    AuDropboxUploadManager.prototype.addFiles = function addFiles(files) {
        var self = this;
        self._files = this._files.concat(files);
        _.forEach(files, function (file) {
            var fileUpload = new AuDropboxFileUpload(self._client, file.id, file.input, file.path);
            fileUpload.onProgress(self._onStatusChanged.bind(self));
            self._filesUploads.push(fileUpload);
        });
        self._start();
    };

    AuDropboxUploadManager.prototype.addFile = function addFile(file) {
        this.addFiles([file]);
    };

    AuDropboxUploadManager.prototype._start = function _start() {
        var self = this;
        if (!self._activePromise) {
            _.forEach(self._filesUploads, function (upload) {
                if (upload.isReady()) {
                    var promise = upload.start();
                    if (promise) {
                        self._activePromise = promise;
                        self._activePromise
                            .then(function () {
                                delete self._activePromise;
                                self._start();
                            })
                            .catch(function () {
                                delete self._activePromise;
                                self._start();
                            });
                    }
                    return false;
                }
            });
        }
    };

    AuDropboxUploadManager.prototype.getUploads = function getUploads() {
        return this._filesUploads;
    };

    AuDropboxUploadManager.prototype._onStatusChanged = function _onStatusChanged(id, options) {
        if (typeof this._statusListener === 'function') {
            this._statusListener(id, options);
        }
    };

    return AuDropboxUploadManager;
});
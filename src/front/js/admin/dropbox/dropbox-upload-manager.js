define([
    'dropbox/dropbox-file-upload'
], function (AuDropboxFileUpload) {
    'use strict';

    function AuDropboxUploadManager(client, options, statusListener, batchUploadedListener) {
        this._client = client;
        this._filesUploads = [];
        this._statusListener = statusListener;
        this._batchUploadedListener = batchUploadedListener;
        this._options = Object.assign({
            autoRelease: true
        }, options || {});
    }

    AuDropboxUploadManager.prototype.addFiles = function addFiles(files) {
        var self = this;
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
                                self.releaseUpload(upload.getId());
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
            if (!self._activePromise && self._filesUploads.length) {
                var upload = self._filesUploads[self._filesUploads.length - 1];
                var lastParentDir = upload.getParentDir();
                self._onFilesBatchUploaded({lastParentDir: lastParentDir});
            }
        }
    };

    AuDropboxUploadManager.prototype.releaseUpload = function _releaseJob(id) {
        var self = this;
        if (self._options.autoRelease === true) {
            var index = self._filesUploads.length - 1;
            while(index >= 0) {
                var upload = self._filesUploads[index];
                if (upload.getId() === id) {
                    upload.release();
                    break;
                }
                index--;
            }
        }
    };

    AuDropboxUploadManager.prototype.getUploads = function getUploads() {
        return this._filesUploads;
    };

    AuDropboxUploadManager.prototype.getUpload = function getUpload(id) {
        return _.find(this._filesUploads, function(upload) {
            return upload.getId() === id;
        });
    };

    AuDropboxUploadManager.prototype._onStatusChanged = function _onStatusChanged(id, options) {
        if (typeof this._statusListener === 'function') {
            this._statusListener(id, options);
        }
    };

    AuDropboxUploadManager.prototype._onFilesBatchUploaded = function _onFilesBatchUploaded(options) {
        if (typeof this._batchUploadedListener === 'function') {
            this._batchUploadedListener(options);
        }
    };

    return AuDropboxUploadManager;
});
'use strict';
define([], function() {

    function onError(error) {
        var message = typeof error === 'string' ? error : (error.message ? error.message : 'Unknown error');
        var stack = error.stack ? '<br>' + error.stack : '';
        dhtmlx.alert({
            title: 'Alert',
            type: 'alert-error',
            text: '<span style="word-break: break-all">' + message + stack + '</span>'
        });
    }
    function AuDropboxFileUpload(client, id, inputFile, path) {
        this._client = client;
        this._progress = [];
        this._id = id;
        this._inputFile = inputFile;
        this._path = path;
        this._statuses = {
            ready: 'ready',
            started: 'started',
            failed: 'failed',
            finished: 'finished',
            released: 'released'
        };
        this._isReleased = false;
        this._uploadFailedTime = 0;
        this._uploadStartedTime = 0;
        this._uploadFinishedTime = 0;
        this._statusDescription = {};
        this._status = this._statuses.ready;
    }

    AuDropboxFileUpload.prototype.start = function start() {
        if (this._status === this._statuses.ready) {
            this._started = true;
            return this._upload();
        }
    };

    AuDropboxFileUpload.prototype.getId = function getId() {
        return this._id;
    };

    AuDropboxFileUpload.prototype.isStarted = function isStarted() {
        return this._status === this._statuses.started;
    };

    AuDropboxFileUpload.prototype.isFailed = function isStarted() {
        return this._status === this._statuses.failed;
    };

    AuDropboxFileUpload.prototype.isFinished = function isFinished() {
        return this._status === this._statuses.finished;
    };

    AuDropboxFileUpload.prototype.isReady = function isReady() {
        return this._status === this._statuses.ready;
    };

    AuDropboxFileUpload.prototype.isReleased = function isReady() {
        return this._isReleased;
    };

    AuDropboxFileUpload.prototype._readFileToBase64 = function _readFileToBase64() {
        var self = this;
        return new Promise(function(resolve, reject) {
            var reader = new FileReader();
            reader.onload = function (event) {
                resolve(event.target.result);
            };
            reader.onerror = function(error) {
                reject(error);
            };
            reader.readAsArrayBuffer(self._inputFile);
        });
    };

    AuDropboxFileUpload.prototype._upload = function _upload() {
        var self = this;
        if (self.isReleased()) {
            return;
        }
        self._status = self._statuses.started;
        self._progressChanged();
        self._uploadStartedTime = Date.now();
        return self._readFileToBase64()
            .then(function(base64) {
                return self._client.filesUpload({
                    contents: base64,
                    path: self._path,
                    //mode: '',
                    autorename: true
                });
            })
            .catch(function(error) {
                onError(error);
                self._uploadFailedTime = Date.now();
                self._statusDescription = error;
                self._status = self._statuses.failed;
                self._progressChanged();
            })
            .then(function(response) {
                self._uploadFinishedTime = Date.now();
                self._statusDescription = response;
                self._status = self._statuses.finished;
                self._progressChanged();
            })
    };

    AuDropboxFileUpload.prototype.onProgress = function onProgress(callback) {
        this._progress.push(callback);
    };

    AuDropboxFileUpload.prototype._progressChanged = function _progressChanged() {
        var self = this;
        setTimeout(function() {
            self._progress.forEach(function(listener) {
                if (typeof listener === 'function') {
                    listener(self._id, {
                        status: self._status,
                        description: self._statusDescription,
                        failedTime: self._uploadFailedTime,
                        startedTime: self._uploadStartedTime,
                        finishedTime: self._uploadFinishedTime
                    });
                }
            });
        }, 0);
    };

    AuDropboxFileUpload.prototype.getParentDir = function getParentDir() {
        return this._path.replace(/\/?[^\/]*$/g, '');
    };

    AuDropboxFileUpload.prototype.release = function release() {
        var self = this;
        self._inputFile = null;
        self._client = null;
        self._isReleased = true;
    };

    return AuDropboxFileUpload;
});
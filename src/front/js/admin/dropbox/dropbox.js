define([
    'dropbox-sdk',
    'common/toast',
    'filesize'
], function (DropboxSdk, Toast, filesize) {
    function getMaxSize() {
        var ratio = 0.9;
        var w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
        var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
        return {
            width: w * ratio,
            height: h * ratio
        }
    }

    function AuDropboxDir() {
        this._preparedCache = {};
        this._client = new DropboxSdk.Dropbox({accessToken: 'EoKj4M04V54AAAAAAAAaVZfQXj4qWQ1TSmSVSW4Qm203zf6DKsS1woE4XyScO-9z'});
        this._rootDir = '/augustova';
        this._currentDir = this._rootDir;
        this._pageSize = 200;
        this._thumbnailsPageSize = 20;
        this._pageNum = 0;
        this._win;
    }

    AuDropboxDir.prototype.openPopup = function openPopup() {
        if (!this._win) {
            this._createWindow();
            this._createDataView();
            this._createStatusBar();
        }
        // this._view.refresh();

        this._loadDir();
    };

    AuDropboxDir.prototype._createWindow = function _createWindow() {
        var wins = new dhtmlXWindows();
        var size = getMaxSize();
        this._win = wins.createWindow("w1", 20, 30, size.width, size.height);
        this._win.centerOnScreen();
        this._win.setModal(true);
        this._win.setMaxDimension(size.width, size.height);
        this._win.setText('Dropbox images');
    };

    AuDropboxDir.prototype._createStatusBar = function _createStatusBar () {
        this._statusBar = this._win.attachStatusBar({height: 20});
    };

    AuDropboxDir.prototype._createDataView = function _createDataView() {
        this._view = this._win.attachDataView({
            drag: false,
            select: true,
            type: {
                template: "\
                <div class='dropbox-item'>\
                    <img src='#icon#' class='dropbox-item-img' alt='#name#'>\
                    <div class='dropbox-item-name'>#name#</div>\
                    <div class='dropbox-item-size'>#size#</div>\
                </div>",
                width: 150,
                height: 150
            }
        });

        this._view.$view.className += ' dropbox-data-view';

        this._initDataViewEvents();
        return this._view;
    };

    AuDropboxDir.prototype._initDataViewEvents = function _initDataViewEvents() {
        var self = this;
        this._view.attachEvent('onItemDblClick', function (id, event, html) {
            var data = self._view.get(id);
            if (data && (data.tag === 'folder' || data.code === 'back')) {
                self._currentDir = data.path;
                self._loadDir();
            }
        });
        /*        this._view.attachEvent('onXLS', function () {
         this._currentDir++;
         this._win.progressOn();
         self._client.filesListFolder({
         path: this._currentDir,
         limit: this._pageSize
         }).then(function (response) {
         if (response.entries && response.entries.length) {
         var forThumbnails = self._extractFilesForThumbnails(response.entries);
         if (forThumbnails.length) {
         return self._loadThumbnails(forThumbnails);
         } else {
         return response.entries;
         }
         }
         }).then(function (items) {
         self._renderRawItems(items);
         });
         });*/
    };

    AuDropboxDir.prototype._renderRawItems = function _renderItems(items) {
        var self = this;
        var prepared = self._prepareRawDropboxDataForDataView(items);
        self._view.clearAll();
        if (self._canGoBack()) {
            prepared.unshift(self._getBackItem());
        }
        self._view.parse(prepared, 'json');
    };

    AuDropboxDir.prototype._extractFilesPathesForThumbnails = function _extractFilesPathesForThumbnails(items) {
        var result = [];
        for (var index = 0; index < items.length; index++) {
            var item = items[index];
            if (item['.tag'] === 'file' && /\.(png|jpeg|jpg)$/gi.test(item.name)) {
                result.push(item.path_lower);
            }
        }
        return result;
    };

    AuDropboxDir.prototype._loadDir = function _loadDir() {
        var self = this;
        this._win.progressOn();
        if (self._preparedCache[this._currentDir]) {
            self._view.clearAll();
            var items = self._preparedCache[this._currentDir];
            if (self._canGoBack()) {
                items = [self._getBackItem()].concat(items);
            }
            self._view.parse(items, 'json');
            self._win.progressOff();
        } else {
            return this._client.filesListFolder({
                path: this._currentDir,
                limit: this._pageSize
            }).then(function (response) {
                var forThumbnails = self._extractFilesPathesForThumbnails(response.entries);
                if (forThumbnails.length) {
                    self._loadThumbnails(self._currentDir, forThumbnails);
                }
                var prepared = self._prepareRawDropboxDataForDataView(response.entries);
                self._view.clearAll();
                self._updateCache(self._currentDir, prepared);
                var items = prepared;
                if (self._canGoBack()) {
                    items = [self._getBackItem()].concat(items);
                }
                self._view.parse(items, 'json');
                self._win.progressOff();
            }).catch(function (error) {
                self._win.progressOff();
                Toast.error(error.error);
            });
        }
    };

    AuDropboxDir.prototype._prepareRawDropboxDataForDataView = function _prepareRawDropboxDataForDataView(data) {
        var self = this;
        return data.map(function (item) {
            return {
                id: item.id,
                code: item.id,
                tag: item['.tag'],
                path: item.path_lower,
                name: item.name,
                icon: self._getFileIcon(item['.tag']),
                size: item.size ? filesize(item.size).human() : ''
            };
        });
    };

    AuDropboxDir.prototype._getFileIcon = function _getFileIcon(tagName) {
        switch (tagName) {
            case 'file':
                return '/images/icons/document.png';
            case 'folder':
                return '/images/icons/folder.png';
            case 'back':
                return '/images/icons/back.png';
        }
        return '';
    };

    AuDropboxDir.prototype._canGoBack = function _canGoBack() {
        var self = this;
        return self._currentDir.indexOf(self._rootDir) === 0 && self._currentDir.trim().length > self._rootDir.trim().length;
    };

    AuDropboxDir.prototype._getBackItem = function _getBackItem() {
        return {
            id: 'back',
            name: 'Назад',
            icon: this._getFileIcon('back'),
            code: 'back',
            size: '',
            path: this._currentDir.replace(/\/?[^\/]*$/g, '')
        };
    };

    AuDropboxDir.prototype._loadThumbnails = function _loadThumbnails(pathDir, filesPathes) {
        var self = this;
        var entries = self._getArgsForThumbnails(filesPathes);
        var responses = 0;

        function apply(response) {
            var entries = response.entries;
            if (entries && entries.length) {
                self._updateThumbnails(pathDir, entries);
            }
            responses--;
            if (responses === 0) {
                self._thumbnailsProgressOff();
            }
        }

        function onError() {
            self._thumbnailsProgressOff();
        }

        if (filesPathes.length <= this._thumbnailsPageSize) {
            self._thumbnailsProgressOn();
            responses = 1;
            self._client.filesGetThumbnailBatch({entries: entries}).then(apply).catch(onError);
        } else {
            var num = Math.ceil(filesPathes.length / this._thumbnailsPageSize) - 1;
            responses = num + 1;
            self._thumbnailsProgressOn();
            while (num >= 0) {
                self._client.filesGetThumbnailBatch(
                    {entries: entries.splice(num * this._thumbnailsPageSize, this._thumbnailsPageSize)}
                ).then(apply).catch(onError);
                num--;
            }
        }
    };

    AuDropboxDir.prototype._getArgsForThumbnails = function _getArgsForThumbnails(pathes) {
        return pathes.map(function (path) {
            return {
                path: path,
                format: 'jpeg',
                size: 'w64h64'
            }
        });
    };

    AuDropboxDir.prototype._updateThumbnails = function _updateViewWithThumbnails(dirPath, thumbnails) {
        var self = this;
        var cacheItems = self._preparedCache[dirPath] || [];
        if (cacheItems.length) {
            for (var index = 0; index < thumbnails.length; index++) {
                var thumbnail = thumbnails[index];
                for (var cacheIndex = 0; cacheIndex < cacheItems.length; cacheIndex++) {
                    var cache = cacheItems[cacheIndex];
                    if (cache.id === thumbnail.metadata.id) {
                        cache.icon = 'data:image/jpeg;base64,' + thumbnail.thumbnail;
                        self._view.set(cache.id, cache);
                        break;
                    }
                }
            }
        }

    };

    AuDropboxDir.prototype._thumbnailsProgressOn = function _thumbnailsProgressOn() {
        if (!this._thumbnailsProgress) {
            var self = this;
            var label = 'Подгрузка изображений';
            var dots = '.';
            this._statusBar.setText(label + dots);
            var index = 0;
            this._thumbnailsProgress = setInterval(function () {
                dots += '.';
                self._statusBar.setText(label + dots);
                if (index === 2) {
                    dots = '';
                    index = 0;
                }
            }, 500);
        }
    };

    AuDropboxDir.prototype._thumbnailsProgressOff = function _thumbnailsProgressOff() {
        var self = this;
        clearInterval(this._thumbnailsProgress);
        this._thumbnailsProgress = null;
        self._statusBar.setText('');
    };

    AuDropboxDir.prototype._updateCache = function _updateCache(path, preparedData) {
        this._preparedCache[path] = preparedData;
    };

    return AuDropboxDir;
});

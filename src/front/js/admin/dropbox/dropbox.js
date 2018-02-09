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
            this._createLayout();
            this._createStatusBar();
            this._createToolBar();
            this._createDataView();
            this._createPreview();
        }
        // this._view.refresh();

        this._loadDir();
    };

    AuDropboxDir.prototype._createLayout = function _createLayout() {
        this._layout = this._win.attachLayout({
            pattern: '2U',
            cells: [
                {id: 'a', header: false},
                {id: 'b', text: 'Просмотр', collapse: true, width: 300, header: true}
            ]
        });
        this._layout.setOffsets({
            top: 0,
            right: 0,
            bottom: 0,
            left: 0
        });
    };

    AuDropboxDir.prototype._createWindow = function _createWindow() {
        var wins = new dhtmlXWindows();
        var size = getMaxSize();
        this._win = wins.createWindow('w1', 20, 30, size.width, size.height);
        this._win.centerOnScreen();
        this._win.setModal(true);
        this._win.setMaxDimension(size.width, size.height);
        this._win.setText('Dropbox images');
    };

    AuDropboxDir.prototype._createStatusBar = function _createStatusBar() {
        this._statusBar = this._win.attachStatusBar({height: 20});
    };

    AuDropboxDir.prototype._createPreview = function _createPreview() {
        var cellB = this._layout.cells('b');
        this._previewCache = {};
        this._previewId = 'dropbox-image-side-preview';
        this._preview = cellB.attachHTMLString('<div id="' + this._previewId + '"></div>');
        cellB.cell.className += ' dropbox-image-side-preview-container';
    };

    AuDropboxDir.prototype._createToolBar = function _createToolBar() {
        var self = this;
        this._toolbar = this._layout.attachToolbar({
            icon_path: '/images/icons/',
            items: [
                {id: 'back', type: 'button', text: 'Назад', img: 'back.png', img_disabled: 'new_dis.gif'},
                {id: 'reload', type: 'button', text: 'Обновить', img: 'reload.png'},
                {id: 'create_dir', type: 'button', text: 'Создать папку', img: 'create_dir.png'},
                {id: 'delete', type: 'button', text: 'Удалить', img: 'delete.png'},
                {id: 'rename', type: 'button', text: 'Переименовать', img: 'rename.png'}
            ]
        });
        this._toolbar
            .attachEvent('onClick', function (id) {
                switch (id) {
                    case 'back':
                        self._goBack();
                        break;
                    case 'reload':
                        self._loadDir(true);
                        break;
                    case 'create_dir':
                        self._createDirDialog();
                        break;
                    case 'delete':
                        self._deleteDialog();
                        break;
                    case 'rename':
                        self._renameDialog();
                }
            });
    };


    AuDropboxDir.prototype._createDataView = function _createDataView() {
        this._view = this._layout.cells('a').attachDataView({
            drag: false,
            select: 'multiselect',
            type: {
                template: "\
                <div class='dropbox-item'>\
                    <div class='dropbox-item-img-container'>\
                        <img src='#icon#' class='dropbox-item-img' alt='#name#'>\
                    </div>\
                    <div class='dropbox-item-text-container'>\
                        <div class='dropbox-item-name'>#label#</div>\
                        <div class='dropbox-item-size'>#size#</div>\
                    </div>\
                </div>",
                width: 150,
                height: 200
            }
        });

        this._view.$view.className += ' dropbox-data-view';

        this._initDataViewEvents();
        return this._view;
    };

    AuDropboxDir.prototype._initDataViewEvents = function _initDataViewEvents() {
        var self = this;
        var cell = self._layout.cells('b');
        this._view.attachEvent('onItemDblClick', function (id) {
            var data = self._view.get(id);
            if (data && data.tag === 'folder') {
                self._currentDir = data.path;
                self._loadDir();
            } else if (data && data.tag === 'file' && cell.isCollapsed()) {
                cell.expand();
                self._openPreview(data.path);
            }
        });
        this._view.attachEvent('onItemClick', function (id) {
            var data = self._view.get(id);
            var cell = self._layout.cells('b');
            if (data && data.tag === 'file' && !cell.isCollapsed()) {
                self._openPreview(data.path, data.name);
            }
        });

    };

    AuDropboxDir.prototype._renderRawItems = function _renderItems(items) {
        var self = this;
        var prepared = self._prepareRawDropboxDataForDataView(items);
        self._view.clearAll();
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

    AuDropboxDir.prototype._goBack = function _goBack() {
        if (this._canGoBack()) {
            this._currentDir = this._currentDir.replace(/\/?[^\/]*$/g, '');
            this._loadDir();
        }
    };

    AuDropboxDir.prototype._loadDir = function _loadDir(force) {
        var self = this;
        this._win.progressOn();
        this._win.setText(this._currentDir);
        if (!force && self._preparedCache[this._currentDir]) {
            self._view.clearAll();
            var items = self._preparedCache[this._currentDir];
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
                self._view.parse(prepared, 'json');
                self._win.progressOff();
            }).catch(function (error) {
                self._win.progressOff();
                Toast.error(error.error);
            });
        }
    };

    AuDropboxDir.prototype._createDirDialog = function _createDirDialog() {
        var self = this;
        var result = prompt('Новая папка', 'Новая папка');
        var cell = this._layout;
        if (result) {
            cell.progressOn();
            this._createDir(self._currentDir + '/' + result)
                .then(function() {
                    self._loadDir(true);
                    cell.progressOff();
                })
                .catch(function(error) {
                    cell.progressOff();
                })
        }
    };

    AuDropboxDir.prototype._deleteDialog = function _deleteDialog() {
        var self = this;
        var selected = this._view.getSelected(true);
        if (selected.length) {
            var items = selected.map(function(id) {
                return '\'' + self._view.get(id).name + '\'';
            });
            var result = confirm('Вы уверены что хотите удалить?\n' + items.join('\n'));
            if (result) {
                var cell = self._layout;
                var pathes = selected.map(function(id) {
                    return self._view.get(id).path;
                });
                cell.progressOn();
                self._deleteItems(pathes)
                    .then(function() {
                        self._releaseCacheFor(pathes);
                        setTimeout(function() {
                            self._loadDir(true);
                            cell.progressOff();
                        }, 1000);
                    })
                    .catch(function() {
                        cell.progressOff();
                    })
            }
        }
    };

    AuDropboxDir.prototype._renameDialog = function _renameDialog() {
        var self = this;
        var selected = this._view.getSelected(true);
        if (selected.length) {
            var data = this._view.get(selected[0]);
            var result = promt('Переименовать \'' + data.name + '\'', data.name);
            if (result && result.trim() !== data.name) {
                var cell = self._layout;
                var pathes = selected.map(function(id) {
                    return self._view.get(id).path;
                });
                cell.progressOn();
                self._deleteItems(pathes)
                    .then(function() {
                        self._releaseCacheFor(pathes);
                        setTimeout(function() {
                            self._loadDir(true);
                            cell.progressOff();
                        }, 1000);
                    })
                    .catch(function() {
                        cell.progressOff();
                    })
            }
        }
    };

    AuDropboxDir.prototype._deleteItems = function _deleteItems(itemsPathes) {
        var self = this;
        var files = itemsPathes.map(function(file) {
            return {path: file};
        });
        return self._client.filesDeleteBatch({entries: files});
    };

    AuDropboxDir.prototype._createDir = function _createDir(path) {
        var self = this;
        return self._client.filesCreateFolder({path: path, autorename: true});
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
                label: item.name.length < 30 ? item.name : item.name.substr(0, 30) + '...',
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
                size: 'w128h128'
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

    AuDropboxDir.prototype._loadThumbnail = function _loadThumbnail(path) {
        var self = this;
        var cell = this._layout.cells('b');
        cell.progressOn();
        return self._client.filesGetThumbnail({
            path: path,
            format: 'png',
            size: 'w640h480'
        })
            .then(function (data) {
                cell.progressOff();
                return data;
            })
            .catch(function () {
                cell.progressOff();
            });
    };

    AuDropboxDir.prototype._thumbnailsProgressOff = function _thumbnailsProgressOff() {
        var self = this;
        clearInterval(this._thumbnailsProgress);
        this._thumbnailsProgress = null;
        self._statusBar.setText('');
    };

    AuDropboxDir.prototype._releaseCacheFor = function _releaseCacheFor(pathes) {
        var self = this;
        pathes.forEach(function(path) {
            var keys = Object.keys(self._preparedCache);
            keys.forEach(function(key) {
                if (key.indexOf(path) === 0) {
                    delete self._preparedCache[key];
                }
            });
        });
    };

    AuDropboxDir.prototype._updateCache = function _updateCache(path, preparedData) {
        this._preparedCache[path] = preparedData;
    };

    AuDropboxDir.prototype._openPreview = function _openPreview(path, name) {
        var self = this;
        if (!self._hasThumbnailSupport(path)) {
            this._setDefaultPreviewImage(name);
        } else if (this._previewCache[path]) {
            var cache = this._previewCache[path];
            this._setPreviewImage(cache.name, cache.data);
        } else {
            var cell = self._layout.cells('b');
            this._loadThumbnail(path)
                .then(function (data) {
                    cell.progressOn();
                    var reader = new FileReader();
                    reader.readAsDataURL(data.fileBlob);
                    reader.onloadend = function() {
                        self._previewCache[path] = {name: data.name, data: reader.result};
                        self._setPreviewImage(data.name, reader.result);
                        cell.progressOff();
                    };
                    reader.onerror = function() {
                        cell.progressOff();
                    };
                })
                .catch(function() {
                    cell.progressOff();
                });
        }
    };

    AuDropboxDir.prototype._setPreviewImage = function _openPreview(name, data) {
        document.getElementById(this._previewId).innerHTML = '<img src="' + data + '">';
        this._layout.cells('b').setText('Просмотр - ' + name);
    };

    AuDropboxDir.prototype._setDefaultPreviewImage = function _setDefaultPreviewImage(name) {
        this._setPreviewImage(name, '/images/icons/document.png');
    };

    AuDropboxDir.prototype._hasThumbnailSupport = function(name) {
        return /\.(jpg|jpeg|png|mpeg)$/g.test(name);
    };


    return AuDropboxDir;
});

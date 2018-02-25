define([
    'common/observable',
    'common/services',
    'dropbox-sdk',
    'common/toast',
    'filesize',
    'dropbox/dropbox-upload-manager'
], function (Observable, Services, DropboxSdk, Toast, filesize, AuDropboxUploadManager) {
    function getMaxSize() {
        var ratio = 0.9;
        var w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
        var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
        return {
            width: w * ratio,
            height: h * ratio
        };
    }

    function AuDropboxDir(rootDir) {
        this._observable = new Observable();
        this._events = {
            addToProduct: 'addToProduct'
        };
        this._preparedCache = {};
        this._rootDir = rootDir || '/augustova';
        this._currentDir = this._rootDir;
        this._pageSize = 500;
        this._thumbnailsPageSize = 20;
        this._pageNum = 0;
        this._options = {
            maxDeleteBachTimes: 10,
            deleteBatchCheckInterval: 500 //milliseconds
        };
        this._uploadStatusImages = {
            started: '/images/icons/upload.png',
            finished: '/images/icons/done.png',
            failed: '/images/icons/failed.png',
            ready: '/images/icons/minus.png'
        };
        $(document.body).append('<div class="dropbox-upload-input"></div>');
        this._win;
        this._createClient();
    }

    AuDropboxDir.prototype.open = function open() {
        var self = this;
        if (!self._win) {
            self._createWindow();
            self._createLayout();
            self._createStatusBar();
            self._createToolBar();
            self._createDataView();
            self._createCellBTabbar();
            self._createPreview();
            self._createUploads();
            self._createUploadsToolbar();
        } else {
            self._win.show();
            self._win.setModal(true);
        }

        self._loadDir();
    };

    AuDropboxDir.prototype.hide = function hide() {
        var self = this;
        if (self._win) {
            self._win.hide();
            self._win.setModal(false);
        }
    };

    AuDropboxDir.prototype.showAddToProductButton = function showAddToProductButton() {
        this._toolbar.showItem('sep1');
        this._toolbar.showItem('add-to-product');
    };

    AuDropboxDir.prototype.hideAddToProductButton = function hideAddToProductButton() {
        this._toolbar.hideItem('sep1');
        this._toolbar.hideItem('add-to-product');
    };

    AuDropboxDir.prototype.onAddToProduct = function onAddToProduct(callback) {
        this._observable.addListener(this._events.addToProduct, callback);
    };

    AuDropboxDir.prototype._createClient = function _createClient() {
        var self = this;
        Services.getAdminSettings()
            .then(function (preferences) {
                var token = preferences.dropbox_access_token;
                self._rootDir = preferences.dropbox_root_directory || self._rootDir;
                self._client = new DropboxSdk.Dropbox({accessToken: token});
                self._uploadsManager = new AuDropboxUploadManager(
                    self._client, {},
                    self._onUploadProgress.bind(self),
                    self._onFilesBatchUploaded.bind(self)
                );
            })
            .catch(Toast.error);
    };

    AuDropboxDir.prototype._createLayout = function _createLayout() {
        this._layout = this._win.attachLayout({
            pattern: '2U',
            cells: [
                {id: 'a', header: false},
                {id: 'b', width: 400, header: false}
            ]
        });
        this._layout.setOffsets({
            top: 0,
            right: 0,
            bottom: 0,
            left: 0
        });
    };

    AuDropboxDir.prototype._createCellBTabbar = function _createCellBTabbar() {
        var cellB = this._layout.cells('b');
        this._cellBTabbar = cellB.attachTabbar({
            align: 'left',
            mode: 'top',
            tabs: [
                {id: 'preview', text: 'Просмотр', active: true},
                {id: 'uploads', text: 'Загрузки'}
            ]
        });

    };

    AuDropboxDir.prototype._createUploads = function _createUploads() {
        var self = this;
        var cell = self._cellBTabbar.tabs('uploads');
        var grid = cell.attachGrid();
        grid.setImagePath('images/icons/');
        grid.setHeader(['Файл', 'Прогресс', 'Время загрузки']);
        grid.setInitWidths('200,100,100');
        grid.setColAlign('left,left,left');
        grid.setColTypes('ro,ro,img');
        grid.setColSorting('str,str');
        grid.init();
        self._uploadsGrid = grid;
        return self._uploadsGrid;
    };

    AuDropboxDir.prototype._createUploadsToolbar = function _createUploadsToolbar() {
        var self = this;
        var cell = self._cellBTabbar.tabs('uploads');
        var toolbar = cell.attachToolbar({
            icon_path: '/images/icons/',
            items: [
                {id: 'clear', type: 'button', text: 'Очистить', img: 'clear.png', img_disabled: 'clear_dis.gif'},
                {
                    id: 'clear_permanently',
                    type: 'button',
                    text: 'Отменить все',
                    img: 'cancel.png',
                    img_disabled: 'cancel_dis.gif'
                }
            ]
        });
        toolbar.attachEvent('onClick', function (id) {
            switch (id) {
                case 'clear':
                    self._clearFinishedUploads();
                    break;
                case 'clear_permanently':
                    self._clearAllUploads();
                    break;
            }
        });
        self._uploadTollbar = toolbar;
    };

    AuDropboxDir.prototype._createWindow = function _createWindow() {
        var self = this;
        var wins = new dhtmlXWindows();
        var size = getMaxSize();
        self._win = wins.createWindow('w1', 20, 30, size.width, size.height);
        self._win.centerOnScreen();
        self._win.setModal(true);
        self._win.setMaxDimension(size.width, size.height);
        self._win.setText('Dropbox images');
        self._win.attachEvent('onClose', function () {
            self._win.hide();
            self._win.setModal(false);
            return false;
        });
        window.onresize = function () {
            var size = getMaxSize();
            self._win.setMaxDimension(size.width, size.height);
            self._win.setDimension(size.width, size.height);
            self._win.centerOnScreen();
        };
    };

    AuDropboxDir.prototype._createStatusBar = function _createStatusBar() {
        this._statusBar = this._win.attachStatusBar({height: 20});
    };

    AuDropboxDir.prototype._createPreview = function _createPreview() {
        var cellB = this._cellBTabbar.tabs('preview');
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
                {id: 'back', type: 'button', text: 'Назад', img: 'back.png', img_disabled: 'back_dis.png'},
                {id: 'reload', type: 'button', text: 'Обновить', img: 'reload.png', img_disabled: 'reload_dis.png'},
                {id: 'create-dir', type: 'button', text: 'Создать папку', img: 'create_dir.png', img_disabled: 'create_dir_dis.png'},
                {id: 'delete', type: 'button', text: 'Удалить', img: 'delete.png', img_disabled: 'delete_dis.png'},
                {id: 'rename', type: 'button', text: 'Переименовать', img: 'edit.png', img_disabled: 'edit_dis.png'},
                {id: 'upload', type: 'button', text: 'Загрузить файлы', img: 'upload.png', img_disabled: 'upload_dis.png'},
                {id: 'sep1', type: 'separator'},
                {id: 'add-to-product', type: 'button', text: 'Добавить к товару', img: 'add.png', img_disabled: 'add_dis.png'}
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
                    case 'create-dir':
                        self._createDirDialog();
                        break;
                    case 'delete':
                        self._deleteDialog();
                        break;
                    case 'rename':
                        self._renameDialog();
                        break;
                    case 'upload':
                        self._fileUploadDialog();
                        break;
                    case 'add-to-product':
                        self._addToProduct();
                        break;
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
            this._client.filesListFolder({
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
                Toast.error(error);
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
                .then(function () {
                    self._loadDir(true);
                    cell.progressOff();
                })
                .catch(function (error) {
                    cell.progressOff();
                    Toast.error(error);
                });
        }
    };

    AuDropboxDir.prototype._deleteDialog = function _deleteDialog() {
        var self = this;
        var selected = this._view.getSelected(true);
        if (selected.length) {
            var items = selected.map(function (id) {
                return '\'' + self._view.get(id).name + '\'';
            });
            var result = confirm('Вы уверены что хотите удалить?\n' + items.join('\n'));
            if (result) {
                var cell = self._layout;
                var pathes = selected.map(function (id) {
                    return self._view.get(id).path;
                });
                cell.progressOn();
                self._deleteItems(pathes)
                    .then(function () {
                        self._releaseCacheForFiles(pathes);
                        setTimeout(function () {
                            self._loadDir(true);
                            cell.progressOff();
                        }, 1000);
                    })
                    .catch(function (error) {
                        cell.progressOff();
                        Toast.error(error);
                    })
            }
        }
    };

    AuDropboxDir.prototype._renameDialog = function _renameDialog() {
        var self = this;
        var selected = this._view.getSelected(true);
        if (selected.length) {
            var data = this._view.get(selected[0]);
            var result = prompt('Переименовать \'' + data.name + '\'', data.name);
            if (result && result.trim() !== data.name && self._isItemNameValid(result)) {
                var cell = self._layout;
                cell.progressOn();
                self._renameItem(data.path, result)
                    .then(function () {
                        self._releaseCacheForFiles([data.path]);
                        setTimeout(function () {
                            self._loadDir(true);
                            cell.progressOff();
                        }, 1000);
                    })
                    .catch(function (error) {
                        cell.progressOff();
                        Toast.error(error);
                    })
            }
        }
    };

    AuDropboxDir.prototype._fileUploadDialog = function _fileUploadDialog() {
        var self = this;
        var id = 'au-dropbox-upload-' + Date.now();
        $('.dropbox-upload-input').html('<input type="file" name="fileToUpload" id="' + id + '" multiple>');
        self._$uploadInput = $('#' + id);
        self._$uploadInput.on('change', function (event) {
            self._cellBTabbar.tabs('uploads').setActive();
            self._uploadFiles(event.target.files);
            $('.dropbox-upload-input').html('');
        });
        self._$uploadInput.trigger('click');
    };

    AuDropboxDir.prototype._addToProduct = function _addToProduct() {
        var selectedIds = this._view.getSelected(true);
        var files = [];
        while(selectedIds.length) {
            var id = selectedIds.pop();
            var itemData = this._view.get(id);
            if (itemData.tag === 'file' && /\.(jpg|jpeg)$/ig.test(itemData.name)) {
                files.push(itemData);
            }
        }
        if (files.length) {
            this._observable.propertyChange(this._events.addToProduct, files);
        }
    };

    AuDropboxDir.prototype._deleteItems = function _deleteItems(itemsPathes) {
        var self = this;
        var files = itemsPathes.map(function (file) {
            return {path: file};
        });
        return self._client.filesDeleteBatch({entries: files})
            .then(function (response) {
                if (response['.tag'] === 'async_job_id') {
                    return self._deleteBatchCheck(response.async_job_id, self._options.maxDeleteBachTimes);
                }
                return response;
            });
    };

    AuDropboxDir.prototype._deleteBatchCheck = function _repeatDeleteBatchCheck(asyncJobId, maxRepeatTime) {
        var self = this;
        maxRepeatTime = maxRepeatTime || 0;
        return self._client.filesDeleteBatchCheck({async_job_id: asyncJobId})
            .then(function (response) {
                var tag = response['.tag'];
                if (tag === 'in_progress' && maxRepeatTime === 0) {
                    throw new Error('Удаление выполняется слишком долго. Обновите папку через 10 секунд.');
                } else if (tag === 'in_progress' && maxRepeatTime > 0) {
                    return new Promise(function (resolve, reject) {
                        setTimeout(function () {
                            self._deleteBatchCheck(asyncJobId, maxRepeatTime - 1)
                                .then(resolve)
                                .catch(reject);
                        }, self._options.deleteBatchCheckInterval);
                    });

                } else if (tag === 'complete') {
                    return true;
                } else if (tag === 'failed') {
                    throw new Error('Удаление завершено с ошибкой. Обновите папку.');
                }
            });
    };

    AuDropboxDir.prototype._renameItem = function _renameItem(itemPath, newName) {
        var self = this;
        var parentCatalog = itemPath.replace(/(.*\/)[^/]*$/g, '$1');
        return self._client.filesMove({
            from_path: itemPath,
            to_path: parentCatalog + newName,
            allow_shared_folder: false,
            autorename: true
        });
    };

    AuDropboxDir.prototype._createDir = function _createDir(path) {
        var self = this;
        return self._client.filesCreateFolder({path: path, autorename: true});
    };

    AuDropboxDir.prototype._uploadFiles = function _uploadFiles(files) {
        var self = this;
        _.forEach(files, function (file) {
            var id = Math.round(Math.random() * 100000000);
            self._uploadsManager.addFile({
                id: id,
                input: file,
                path: self._currentDir + '/' + file.name
            });
            self._uploadsGrid.addRow(id, ['0 сек.', file.name, self._uploadStatusImages.ready]);
        });
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
        return self._currentDir.indexOf(self._rootDir) === 0
            && self._currentDir.trim().length > self._rootDir.trim().length;
    };

    AuDropboxDir.prototype._loadThumbnails = function _loadThumbnails(pathDir, filesPathes) {
        var self = this;
        var entries = self._getArgsForThumbnails(filesPathes);
        var responses = 0;

        function apply(response) {
            var entries = response.entries;
            var successThumbnails = self._filterSuccessThumbnails(response.entries);
            if (successThumbnails && successThumbnails.length) {
                self._updateThumbnails(pathDir, successThumbnails);
            }
            responses--;
            if (responses === 0) {
                self._thumbnailsProgressOff();
            }
        }

        if (filesPathes.length <= this._thumbnailsPageSize) {
            self._thumbnailsProgressOn();
            responses = 1;
            self._client.filesGetThumbnailBatch({entries: entries})
                .then(apply)
                .catch(function (error) {
                    Toast.error(error);
                    self._thumbnailsProgressOff();
                });
        } else {
            var num = Math.ceil(filesPathes.length / this._thumbnailsPageSize) - 1;
            responses = num + 1;
            self._thumbnailsProgressOn();
            while (num >= 0) {
                self._client.filesGetThumbnailBatch(
                    {entries: entries.splice(num * this._thumbnailsPageSize, this._thumbnailsPageSize)}
                )
                    .then(apply)
                    .catch(function (error) {
                        Toast.error(error);
                        self._thumbnailsProgressOff();
                    });
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
            };
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
            .catch(function (error) {
                cell.progressOff();
                Toast.error(error);
            });
    };

    AuDropboxDir.prototype._thumbnailsProgressOff = function _thumbnailsProgressOff() {
        var self = this;
        clearInterval(this._thumbnailsProgress);
        this._thumbnailsProgress = null;
        self._statusBar.setText('');
    };

    AuDropboxDir.prototype._releaseCacheForFiles = function _releaseCacheForFiles(pathes) {
        var self = this;
        var roots = [];
        pathes.forEach(function (filePath) {
            roots.push(filePath);
            var root = self._getFileRootDir(filePath);
            if (roots.indexOf(root) === -1) {
                roots.push(root);
            }
        });
        roots.forEach(function (root) {
            if (self._preparedCache.hasOwnProperty(root)) {
                delete self._preparedCache[root];
            }
        });
    };

    AuDropboxDir.prototype._getFileRootDir = function _getFileRootDir(file) {
        return file.replace(/^(.*)\/[^\/]*$/g, '$1');
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
            var cell = self._cellBTabbar.tabs('preview');
            this._loadThumbnail(path)
                .then(function (data) {
                    if (data) {
                        cell.progressOn();
                        var reader = new FileReader();
                        reader.readAsDataURL(data.fileBlob);
                        reader.onloadend = function () {
                            self._previewCache[path] = {name: data.name, data: reader.result};
                            self._setPreviewImage(data.name, reader.result);
                            cell.progressOff();
                        };
                        reader.onerror = function () {
                            cell.progressOff();
                        };
                    }
                })
                .catch(function (error) {
                    cell.progressOff();
                    Toast.error(error);
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

    AuDropboxDir.prototype._hasThumbnailSupport = function (name) {
        return /\.(jpg|jpeg|png|mpeg)$/g.test(name);
    };

    AuDropboxDir.prototype._isItemNameValid = function (itemName) {
        return !/\//g.test(itemName);
    };

    AuDropboxDir.prototype._onUploadProgress = function _onUploadProgress(id, options) {
        var self = this;
        var rowIndex = self._uploadsGrid.getRowIndex(id);
        if (rowIndex === -1) {
            return;
        }
        var statusImage = self._uploadStatusImages[options.status] || self._uploadStatusImages.ready;
        if (options.status === 'finished') {
            var time = Math.round((options.finishedTime - options.startedTime) / 1000);
            self._uploadsGrid.cells(id, 0).setValue(time + ' сек.');
        }
        self._uploadsGrid.cells(id, 2).setValue(statusImage);
    };

    AuDropboxDir.prototype._onFilesBatchUploaded = function _onFilesBatchUploaded(options) {
        var self = this;
        if (options && options.lastParentDir === self._currentDir) {
            self._loadDir(true);
        }
    };

    AuDropboxDir.prototype._filterSuccessThumbnails = function _filterSuccessThumbnails(thumbnails) {
        return _.filter(thumbnails, function (thumbnail) {
            return thumbnail['.tag'] !== 'failure';
        });
    };

    AuDropboxDir.prototype._clearFinishedUploads = function () {
        var self = this;
        self._uploadsGrid.forEachRow(function (rowId) {
            var upload = self._uploadsManager.getUpload(rowId);
            if (upload && (upload.isFinished() || upload.isFailed())) {
                self._uploadsGrid.deleteRow(rowId);
            }
        });
    };

    AuDropboxDir.prototype._clearAllUploads = function () {
        var self = this;
        self._uploadsGrid.forEachRow(function (rowId) {
            var upload = self._uploadsManager.getUpload(rowId);
            if (upload && (upload.isFinished() || upload.isFailed() || upload.isReady())) {
                self._uploadsGrid.deleteRow(rowId);
                self._uploadsManager.releaseUpload(rowId);
            }
        });
    };

    return AuDropboxDir;
});

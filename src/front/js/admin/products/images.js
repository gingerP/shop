define([
    'lodash',
    'common/services'
], function (_, Services) {

    function initImages(tabbar) {
        tabbar.addTab('b', 'Изображения');
        tabbar.attachEvent("onSelect", function (id, lastId) {
            if (id == 'b') {
                loadImages()
            }
            return true;
        });

        function loadImages() {

        }

        var toolbar = tabbar.tabs('b').attachToolbar({
            items: [
                {id: 'add', type: 'button', pos: 0, text: 'Добавить фото'}
            ],
            onClick: function (id) {
                if (id === 'add') {
                    dataView.openDialog();
                }
            }
        });

        var dataView = tabbar.cells('b').attachDataView({
            type: {
                template: "<div class='image_template'>#image#<div class='title #titleClass#'>#title#</div></div>",
                height: 220,
                width: 130
            }
        });

        dataView._auNewImagesEverAdded = 0;

        dataView.setCustomUserData = function (id, userData) {
            if (!this.customUserData) {
                this.customUserData = {};
            }
            this.customUserData[id] = userData;
        };
        dataView.getCustomUserData = function (id) {
            if (this.customUserData) {
                return this.customUserData[id];
            }
            return null;
        };
        dataView.getItemViewButtons = function (id) {
            return "<div class='move_prev' onclick=\"app.images.movePrev('" + id.trim() + "')\">&lt;</div>" +
                "<div class='move_next' onclick=\"app.images.moveNext('" + id.trim() + "')\">&gt;</div>" +
                "<div class='delete_btn' onclick=\"app.images.removeItem('" + id.trim() + "')\">x</div>";
        };

        dataView.moveNext = function (id) {
            this.moveDown(id);
        };

        dataView.movePrev = function (id) {
            this.moveUp(id);
        };

        dataView.removeItem = function (id) {
            var self = this;
            var data = this.get(id);
            dhtmlx.confirm({
                type: 'confirm-warning',
                ok: 'Да', cancel: 'Нет',
                text: "Вы уверены, что хотите удалить фото '" + data.title + "'?",
                callback: function (result) {
                    if (result) {
                        self.remove(id);
                    }
                }
            });
        };

        dataView.deleteFile = function (id) {
            var userData = this.getCustomUserData(id);
            this.remove(id);
        };

        dataView.openDialog = function () {
            if (!this.isLocked()) {
                var uploader = $('#file_upload');
                if (!uploader.length) {
                    $(document.body).append('<input type="file" name="fileToUpload" id="file_upload" onchange="app.images.uploadFile(this)">').trigger('click');
                }
                $('#file_upload').trigger('click');
            }
        };
        dataView.saveImages = function (id) {
            var index = 0;
            var images = [];
            _.each(this.serialize(), function (image) {
                if (image.isRaw) {
                    images.push({data: image.data, isNew: true});
                } else {
                    images.push({data: image.data, isNew: false});
                }
            });

            app.layout.progressOn();
            return Services.uploadImagesForGood(+id, images)
                .then(function () {
                    app.layout.progressOff();
                    dhtmlx.message({
                        text: 'Изображения для товара успешно сохранены.',
                        expire: 3000,
                        type: 'dhx-message-success'
                    });
                }).catch(function () {
                    app.layout.progressOff();
                });
        };

        dataView.uploadFile = function (e) {
            var instance = this;
            var file = e.files[0];
            var reader = new FileReader();
            reader.onload = function (e) {
                var id = 'new_' + U.getRandomString();
                instance.setCustomUserData(id, {image: reader.result});
                dataView._auNewImagesEverAdded++;
                instance.add({
                    id: id,
                    titleClass: '',
                    title: 'Новое фото #' + dataView._auNewImagesEverAdded,
                    image: '<img src="' + reader.result + '">' + instance.getItemViewButtons(id),
                    data: reader.result,
                    isRaw: true
                });
            };
            reader.readAsDataURL(file);

        };

        dataView.lock = function (state) {
            if (state) {
                toolbar.disableItem('add');
                $(this._obj).addClass('disable').removeClass('enable');
            } else {
                toolbar.enableItem('add');
                $(this._obj).addClass('enable').removeClass('disable');
            }
        };

        dataView.isLocked = function () {
            return $(this._obj).hasClass('disable');
        };

        dataView.clearImages = function () {
            var instance = this;
            dataView._auNewImagesEverAdded = 0;
            instance.clearAll();
            instance.lock(true);
        };

        dataView.loadImages = function (entity) {
            var instance = this;
            var images = entity.images;
            instance.clearAll();
            instance.lock(false);
            toolbar.enableItem('add');
            if (images && images.length) {
                for (var imageIndex = 0; imageIndex < images.length; imageIndex++) {
                    var imageCode = images[imageIndex];
                    var imagePath = 'images/catalog/' + entity.key_item + '/s_' + imageCode + '.jpg';
                    var id = U.getRandomString();
                    instance.add({
                        id: id,
                        titleClass: '',
                        title: imageIndex + 1,
                        image: '<img src="/' + imagePath + '?' + Date.now() + '" class="exist_image">' + instance.getItemViewButtons(id),
                        data: imageCode
                    });
                }
            }
        };

        dataView.syncNewItemsTitle = function updateNewItemsTitle() {
            var newImagesIndex = 0;
        };

        dataView.lock(true);
        return dataView;
    }

    return {
        init: initImages
    };
});

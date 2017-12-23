define([
    'lodash',
    'common/services'
], function(_, Services) {

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

        var dataView = tabbar.cells('b').attachDataView({
            type: {
                template: "<div class='image_template'>#image#<div class='title #titleClass#'>#title#</div></div>",
                height: 220,
                width: 130
            }
        });

        dataView._auImages = [];
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
            var srcIndex = _.findIndex(this._auImages, {id: id});
            var destIndex = 0;
            var imageItem = this._auImages.splice(srcIndex, 1)[0];
            if (srcIndex === this._auImages.length) {
                destIndex = 0;
                this._auImages.unshift(imageItem);
            } else {
                destIndex = srcIndex + 1;
                this._auImages.splice(destIndex, 0, imageItem);
            }
            this.move(id, destIndex);
        };

        dataView.movePrev = function (id) {
            var srcIndex = _.findIndex(this._auImages, {id: id});
            var destIndex = 0;
            var imageItem = this._auImages.splice(srcIndex, 1)[0];
            if (srcIndex === 0) {
                destIndex = this._auImages.length;
                this._auImages.push(imageItem);
            } else {
                destIndex = srcIndex - 1;
                this._auImages.splice(destIndex, 0, imageItem);
            }
            this.move(id, destIndex);
        };

        dataView.removeItem = function (id) {
            var srcIndex = _.findIndex(this._auImages, {id: id});
            this._auImages.splice(srcIndex, 1);
            this.remove(id);
        };

        dataView.deleteFile = function (id) {
            var userData = this.getCustomUserData(id);
            this.remove(id);
        };

        dataView.attachEvent('onBeforeSelect', function (id, state) {
            if (id == 'add') {
                return false;
            } else {

            }
        });
        dataView.attachEvent("onBeforeDrop", function (context, ev) {
            if (context.target === 'add' || context.start === 'add') {
                return false;
            }
        });
        dataView.attachEvent("onBeforeDrag", function (context, ev) {
            if (context.start === 'add') {
                return false;
            }
        });
        dataView.openDialog = function () {
            if (!this.isLocked()) {
                $('#file_upload').trigger('click');
            }
        };
        dataView.saveImages = function (id, callback) {
            var index = 0;
            var images = _.map(this._auImages, function(imageItem, index) {
                return {index: index, new: imageItem.isNew, image: imageItem.data};
            });

            Services.uploadImagesForGood(+id, images, function() {
                dhtmlx.message({
                    text: 'Изображения для товара успешно сохранены.',
                    expire: 3000,
                    type: 'dhx-message-success'
                });
                callback();
            });
        };

        dataView.uploadFile = function (e) {
            var instance = this;
            var file = e.files[0];
            var reader = new FileReader();
            reader.onload = function (e) {
                var id = 'new_' + U.getRandomString();
                instance.setCustomUserData(id, {image: reader.result});
                var imageItem = dataView.addImageItem(id, reader.result, true);
                dataView._auNewImagesEverAdded++;
                instance.add({
                    id: imageItem.id,
                    titleClass: 'image-new',
                    title: 'Новое фото #' + dataView._auNewImagesEverAdded,
                    image: '<img src="' + reader.result + '">' + instance.getItemViewButtons(imageItem.id)
                }, imageItem.position);
            };
            reader.readAsDataURL(file);

        };

        dataView.lock = function (state) {
            if (state) {
                $(this._obj).addClass('disable').removeClass('enable');
            } else {
                $(this._obj).addClass('enable').removeClass('disable');
            }
        };

        dataView.isLocked = function () {
            return $(this._obj).hasClass('disable');
        };

        dataView.clearImages = function () {
            var instance = this;
            instance._auImages = [];
            dataView._auNewImagesEverAdded = 0;
            instance.clearAll();
            instance.addDefaultItem();
            instance.lock(false);
        };

        dataView.loadImages = function (id) {
            var instance = this;
            instance.clearAll();
            instance.addDefaultItem();
            instance.lock(false);
            Services.getGoodImages(id, function (images) {
                dataView._auImages = [];
                dataView._auNewImagesEverAdded = 0;
                if (images && images.length) {
                    for (var imageIndex = 0; imageIndex < images.length; imageIndex++) {
                        var pattern = new RegExp('[^\/]*$');
                        var res = pattern.exec(images[imageIndex]);
                        var imageItem = dataView.addImageItem(U.getRandomString(), images[imageIndex], false);
                        instance.setCustomUserData(imageItem.id, {image: images[imageIndex]});
                        instance.add({
                            id: imageItem.id,
                            titleClass: '',
                            title: res.length ? res[0] : '',
                            image: '<img src="/' + images[imageIndex] + '?' + Date.now() + '" class="exist_image">' + instance.getItemViewButtons(imageItem.id)
                        }, imageItem.position);
                    }
                }
            })
        };

        dataView.addDefaultItem = function () {
            var defaultItem = {
                id: 'add',
                title: '',
                image: "<div onclick='app.images.openDialog(this);' class='image_template image_template_add_btn'>+</div>\
                <form enctype='multipart/form-data' id='file_load_form'>\
                <input type='file' style='visibility:hidden;' name='goods_images' onchange='app.images.uploadFile(this)' id='file_upload' />\
                </form>"
            };
            this.add(defaultItem, 0);
        };

        dataView.addImageItem = function addImageItem(id, data, isNew) {
            var imageItem = {
                id: id,
                isNew: isNew,
                position: dataView._auImages.length,
                newPosition: dataView._auImages.length,
                data: data
            };
            dataView._auImages.push(imageItem);
            return imageItem;
        };

        dataView.syncNewItemsTitle = function updateNewItemsTitle() {
            var newImagesIndex = 0;
            _.forEach(this._auImages, function(image) {
                if (image.isNew) {
                    newImagesIndex++;
                    dataView.update(image.id, {title: 'Новое фото #' + newImagesIndex})
                }
            });
        };

        dataView.addDefaultItem();
        dataView.lock(true);
        return dataView;
    }

    return {
        init: initImages
    };
});

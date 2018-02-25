define([
    'common/components'
], function (Components) {
    'use strict';

    function formatBookletImage(src) {
        if (src && src.length > 0 && src.indexOf('data:image') !== 0) {
            src = '/booklet_images/' + src;
        }
        return src;
    }

    function BookletEditorComponent(parent) {
        this.parent = null;
        this.imageLoaderId = 'file_upload';
        this.imageLoader = '<div id="' + this.imageLoaderId + '"><input class="' + this.imageLoaderId + '" type="file"></div>';
        this.imagePreviewTemplate = "<div id='booklet-item-image-preview'>\
        <image id='booklet_item_image' class='booklet_item_image'/>\
        <div class='text_fields_container'>\
            <div class='text_fields_sub_container top_right'>\
                <textarea id='top_right_0' class='top_right_0 booklet_item_image_label' rows='1'></textarea>\
                <div class='text_field_line'></div>\
                <textarea id='top_right_1' class='top_right_1 booklet_item_image_label' rows='1'></textarea>\
            </div>\
            <div class='text_fields_sub_container top_left'>\
                <textarea id='top_left_0' class='top_left_0 booklet_item_image_label' rows='1'></textarea>\
                <div class='text_field_line'></div>\
                <textarea id='top_left_1' class='top_left_1 booklet_item_image_label' rows='1'></textarea>\
            </div>\
            <div class='text_fields_sub_container bottom_right'>\
                <textarea id='bottom_right_0' class='bottom_right_0 booklet_item_image_label' rows='1'></textarea>\
                <div class='text_field_line'></div>\
                <textarea id='bottom_right_1' class='bottom_right_1 booklet_item_image_label' rows='1'></textarea>\
            </div>\
            <div class='text_fields_sub_container bottom_left'>\
                <textarea id='bottom_left_0' class='bottom_left_0 booklet_item_image_label' rows='1'></textarea>\
                <div class='text_field_line'></div>\
                <textarea id='bottom_left_1' class='bottom_left_1 booklet_item_image_label' rows='1'></textarea>\
            </div>\
        </div>\
    </div>";
    }

    BookletEditorComponent.prototype.init = function (controller, cloud) {
        this.controller = controller;
        /**@type AuDropboxDir*/
        this._cloud = cloud;
        this._cloud.onAddToProduct(this._onAddFromCloud.bind(this));
        return this;
    };

    BookletEditorComponent.prototype.updateRelations = function (parent) {
        this.clear();
        this.parent = parent;
    };

    BookletEditorComponent.prototype._initWinEvents = function _initWinEvents() {
        var self = this;
        this._win.attachEvent('onClose', function () {
            self.clear();
            this.hide();
        });
    };

    BookletEditorComponent.prototype.show = function () {
        if (!this._win) {
            var myWins = new dhtmlXWindows('dhx_blue');
            this._win = myWins.createWindow('item_editor', 500, 450, 660, 610);
            this._initWinEvents();
            this._createForm();
            this._createToolbar();
            this.labelTemplates = this._initTemplates(this._form);
            this._win.setText('Редактирование');
        }
        var position = U.getPageCenter();
        var size = this._win.getDimension();
        var x = position.x - size[0] / 2;
        var y = position.y - size[1] / 2;
        this._win.setPosition(
            x >= 0 ? x : 0,
            y >= 0 ? y : 0
        );
        this._win.show();
    };

    BookletEditorComponent.prototype._resetImageLoader = function () {
        var self = this;
        $('#' + this.imageLoaderId).replaceWith(this.imageLoader);
        $('#' + this.imageLoaderId).on('change', '.' + this.imageLoaderId,
            function () {
                var file = this.files[0];
                var reader = new FileReader();
                reader.onload = function () {
                    self._form.setItemValue('_image', reader.result);
                    $('.booklet_item_image').attr('src', reader.result).data('isBase64', true);
                    self._resetImageLoader();
                };
                reader.readAsDataURL(file);
            });
    };

    BookletEditorComponent.prototype._createForm = function _createForm() {
        var config = [
            {type: 'settings', labelWidth: 50, labelAlign: 'right', inputWidth: 250, position: 'label-left'},
            {
                type: 'template',
                name: '_image_preview',
                className: 'image_preview',
                value: this.imagePreviewTemplate,
                inputWidth: 320,
                inputHeight: 500
            },
            {type: 'newcolumn'},
            {type: 'input', name: 'id', label: 'ID', readonly: true},
            {type: 'input', name: 'number', label: 'Номер'},
            {type: 'button', name: 'image_load_locally', value: 'Загрузить фото c компьютера'},
            {type: 'button', name: 'image_load_cloud', value: 'Загрузить фото из облака'},
            {type: 'template', name: '_file_loader', value: this.imageLoader, hidden: true},
            {type: 'container', name: 'labelsTemplates', inputWidth: 200, inputHeight: 300}
        ];
        this._form = this._win.attachForm(config);
        this._form.templates = {};
        this._initFormEvents();
    };

    BookletEditorComponent.prototype._createToolbar = function _createToolbar() {
        var self = this;
        self._toolbar = self._win.attachToolbar({
            icons_path: '/images/icons/',
            items: [
                {
                    id: 'save',
                    type: 'button',
                    text: 'Применить',
                    img: 'save.png',
                    img_disabled: 'save_dis.png'
                }
            ]
        });
        self._toolbar.attachEvent('onClick', function (buttonId) {
            switch (buttonId) {
                case 'save':
                    var entity = self.getData();
                    self.close();
                    self.parent.controller.updateEntity(entity);
                    self.parent.render();
                    break;
            }
        });
    };

    BookletEditorComponent.prototype._initFormEvents = function _initFormEvents() {
        var self = this;
        self._form.attachEvent('onButtonClick', function (name) {
            switch (name) {
                case 'image_load_locally':
                    self._resetImageLoader();
                    $('#' + self.imageLoaderId + ' .' + self.imageLoaderId).trigger('click');
                    break;
                case 'image_load_cloud':
                    self._cloud.open();
                    break;
            }
        });
        self._form.attachEvent('onButtonClick', function (name) {
            if (name === 'image_inputs_templates') {
                if (!self.popup.isVisible()) {
                    self.popup.show('image_inputs_templates');
                    self.labelTemplates.refresh();
                } else {
                    self.popup.hide();
                }
            }
        });

        $('.text_fields_container textarea').focus(function () {
            $('.text_fields_sub_container').css('z-index', 100);
            $(this).parents('.text_fields_sub_container').css('z-index', 101);
        });
        self._resetImageLoader();
    };

    BookletEditorComponent.prototype.populateData = function (entity) {
        this.controller.setEntity(entity);
        this._populateEntity(entity);
    };

    BookletEditorComponent.prototype.getData = function () {
        var self = this;
        var customUpdater = {
            image: function (form, entity) {
                var $image = $('#booklet_item_image');
                var data = $image.data();
                if (data.isBase64) {
                    entity.image = $image.attr('src');
                }
                if (data.isCloud) {
                    entity.cloudId = data.cloudId;
                    entity.cloudMetaFileExtension = data.cloudMetaFileExtension;
                }
            },
            listLabels: function (form, entity) {
                var notExcistingTypes = Object.keys(self.parent.controller.labelTypes);
                for (var labelIndex = 0; labelIndex < entity.listLabels.length; labelIndex++) {
                    var label = entity.listLabels[labelIndex];
                    var indexLabelNon = notExcistingTypes.indexOf(label.type);
                    if (indexLabelNon >= 0) {
                        notExcistingTypes.splice(indexLabelNon, 1);
                        label.text = $('#' + label.type).val();
                    }
                }
                if (notExcistingTypes.length) {
                    for (var labelIndex = 0; labelIndex < notExcistingTypes.length; labelIndex++) {
                        var label = self.parent.controller.bookletItemLabelManager.createNewEntity();
                        label.type = notExcistingTypes[labelIndex];
                        label.text = $('#' + notExcistingTypes[labelIndex]).val();
                        entity.listLabels.push(label);
                    }
                }
            }
        };
        var entity = this.controller.getEntity();
        Components.updateEntity(this._form, entity, customUpdater);
        return entity;
    };

    BookletEditorComponent.prototype.close = function () {
        this._win.close();
        this.clear();
    };

    BookletEditorComponent.prototype._populateEntity = function (entity) {
        var self = this;
        var customUpdater = {
            image: function (form, entity) {
                if (entity.image && entity.image.length > 0) {
                    var $image = $('#booklet_item_image');
                    if (entity.image.indexOf('data:image') !== 0) {
                        $image.attr('src', formatBookletImage(entity.image));
                    } else {
                        $image.data('isBase64', true);
                        $image.attr('src', entity.image);
                    }
                }
            },
            listLabels: function (form, entity) {
                if (entity.listLabels) {
                    for (var labelIndex = 0; labelIndex < entity.listLabels.length; labelIndex++) {
                        var label = entity.listLabels[labelIndex];
                        if (U.hasContent(label.type) && U.hasContent(label.text)) {
                            var matches = /(.*){1}_\d{1}$/.exec(label.type);
                            if (matches && matches.length > 1) {
                                self.labelTemplates._select_mark(matches[1], true);
                                $('#' + label.type).val(label.text);
                            }
                        }
                    }
                }
            }
        };
        Components.updateFormData(this._form, entity, customUpdater);
    };

    BookletEditorComponent.prototype._updateImageTextareas = function (ids) {
        ids = Array.isArray(ids) ? ids : [ids];
        $('.text_fields_sub_container').hide();
        for (var idIndex = 0; idIndex < ids.length; idIndex++) {
            if (ids[idIndex] !== '') {
                $('.text_fields_sub_container.' + ids[idIndex]).show();
            }
        }
    };

    BookletEditorComponent.prototype._initTemplates = function (form) {
        var self = this;
        var formKey = 'image_inputs_templates';
        var editorFormDataView = new dhtmlXDataView({
            container: this._form.getContainer('labelsTemplates'),
            select: 'multiselect',
            type: {
                template: "<div><img src='#img#'/></div>",
                width: 60,
                height: 100
            }
        });
        $(editorFormDataView.$view).addClass('booklet_item_data_view');
        var templates = [
            {id: 'top_right', img: '/images/icons/booklet-item-text-template-top-right.png'},
            {id: 'top_left', img: '/images/icons/booklet-item-text-template-top-left.png'},
            {id: 'bottom_right', img: '/images/icons/booklet-item-text-template-bottom-right.png'},
            {id: 'bottom_left', img: '/images/icons/booklet-item-text-template-bottom-left.png'}
        ];

        for (var templateIndex = 0; templateIndex < templates.length; templateIndex++) {
            editorFormDataView.add(templates[templateIndex], 0);
        }
        editorFormDataView.attachEvent('onSelectChange', function (ids) {
            self._updateImageTextareas(this.getSelected());
        });
        /*    editorFormDataView.attachEvent('onBeforeSelect', function(id) {
         if (!editorFormDataView.isSelected(id)) {
         editorFormDataView.select(id);
         } else {
         editorFormDataView.unselect(id);
         }
         })*/
        self._updateImageTextareas([]);
        return editorFormDataView;
    };

    BookletEditorComponent.prototype._getFormData = function (form) {
        var formData = form.getFormData();
        $('#booklet-item-image-preview .booklet_item_image_label').each(
            function () {
                var thizz = $(this);
                formData[thizz.attr('id')] = thizz.val();
            }
        );
        return {};
    };

    BookletEditorComponent.prototype._onAddFromCloud = function _onAddFromCloud(imagesInfo) {
        if (imagesInfo && imagesInfo.length) {
            var self = this;
            var imageData = imagesInfo[0];
            var imageBase64 = imageData.info.icon;
            if (imageData.preview) {
                imageBase64 = imageData.preview.data;
            }
            self._form.setItemValue('_image', imageBase64);
            var extension = imageData.info.name.replace(/.*(jpg|jpeg)$/gi, '$1');
            $('.booklet_item_image')
                .attr('src', imageBase64)
                .data({
                    isBase64: true,
                    isCloud: true,
                    cloudId: imageData.info.id,
                    cloudMetaFileExtension: extension
                });
            self._cloud.hide();
        }
    };

    BookletEditorComponent.prototype.clear = function () {
        var self = this;
        if (this._form) {
            this._form.forEachItem(function (name) {
                var type = self._form.getItemType(name);
                if (['input'].indexOf(type) >= 0 && name.indexOf('_') !== 0) {
                    self._form.setItemValue(name);
                }
            });
            $('#booklet-item-image-preview .booklet_item_image_label').val('');
            $('#booklet_item_image')
                .attr('src', '')
                .removeData('isBase64')
                .removeData('isCloud')
                .removeData('cloudId')
                .removeData('cloudMetaFileExtension');
            this._resetImageLoader();
            this.labelTemplates.unselectAll();
        }
        this.controller.clear();
    };

    return BookletEditorComponent;
});
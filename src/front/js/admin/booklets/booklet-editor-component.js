define([
    'common/components'
], function (Components) {
    'use strict';

    function BookletEditorComponent(parent) {
        this.parent = null;
        this.imageLoaderId = 'file_upload';
        this.imageLoader = '<div id="' + this.imageLoaderId + '"><input class="' + this.imageLoaderId + '" type="file"></div>';
        this.imagePreviewTemplate = "<div id='booklet_item_image_preview'>\
        <image id='booklet_item_image' class='booklet_item_image'/>\
        <div class='text_fields_container'>\
            <div class='text_fields_sub_container top_right'>\
                <textarea id='top_right_0' class='top_right_0 booklet_item_image_label' rows='1'></textarea>\
                <div class='text_field_line'></div>\
                <textarea id='top_right_1' class='top_right_1 booklet_item_image_label' rows='1'></textarea>\
            </div>\
            <div class='text_fields_sub_container bottom_right'>\
                <textarea id='bottom_right_0' class='bottom_right_0 booklet_item_image_label' rows='1'></textarea>\
                <div class='text_field_line'></div>\
                <textarea id='bottom_right_1' class='bottom_right_1 booklet_item_image_label' rows='1'></textarea>\
            </div>\
            <div class='text_fields_sub_container bottom_center'>\
                <textarea id='bottom_center_0' class='bottom_center_0 booklet_item_image_label' rows='1'></textarea>\
                <div class='text_field_line'></div>\
                <textarea id='bottom_center_1' class='bottom_center_1 booklet_item_image_label' rows='1'></textarea>\
            </div>\
        </div>\
    </div>";
    }

    BookletEditorComponent.prototype.init = function (controller) {
        this.controller = controller;
        this.labelTemplates = undefined;
        this.window = undefined;
        return this;
    };

    BookletEditorComponent.prototype.updateRelations = function (parent) {
        this.clear();
        this.parent = parent;
    };

    BookletEditorComponent.prototype.initEvents = function () {
        var instance = this;
        this.window.attachEvent('onClose', function () {
            instance.clear();
            this.hide();
        })
    };

    BookletEditorComponent.prototype.show = function () {
        if (typeof this.window == 'undefined') {
            var myWins = new dhtmlXWindows('dhx_blue');
            this.window = myWins.createWindow('item_editor', 500, 450, 660, 565);
            this.initEvents();
            this.form = this._initForm(this.window);
            this.labelTemplates = this._initTemplates(this.form);
        }
        var position = U.getPageCenter();
        var size = this.window.getDimension();
        var x = position.x - size[0] / 2;
        var y = position.y - size[1] / 2;
        this.window.setPosition(
            x >= 0 ? x : 0,
            y >= 0 ? y : 0
        );
        this.window.show();
    };

    BookletEditorComponent.prototype._resetImageLoader = function () {
        var instance = this;
        $('#' + this.imageLoaderId).replaceWith(this.imageLoader);
        $('#' + this.imageLoaderId).on('change', '.' + this.imageLoaderId, function () {
            var file = this.files[0];
            var reader = new FileReader();
            reader.onload = function () {
                instance.form.setItemValue('_image', reader.result);
                $('.booklet_item_image').attr('src', reader.result).data('isbase64', true);
                instance._resetImageLoader();
            }
            reader.readAsDataURL(file);
        })
    };

    BookletEditorComponent.prototype._initForm = function () {
        var instance = this;

        function initEvents(form) {
            form.attachEvent('onButtonClick', function (name) {
                switch (name) {
                    case 'image_load':
                        $('#' + instance.imageLoaderId + ' .' + instance.imageLoaderId).trigger('click');
                        break;
                    case 'ok':
                        var entity = instance.getData();
                        instance.close();
                        instance.parent.controller.updateEntity(entity);
                        instance.parent.render();
                        break;
                }
            })
            form.attachEvent('onButtonClick', function (name) {
                if (name == 'image_inputs_templates') {
                    if (!instance.popup.isVisible()) {
                        instance.popup.show('image_inputs_templates');
                        instance.labelTemplates.refresh();
                    } else {
                        instance.popup.hide();
                    }
                }
            })

            $('.text_fields_container textarea').focus(function () {
                $('.text_fields_sub_container').css('z-index', 100);
                $(this).parents('.text_fields_sub_container').css('z-index', 101);
            })
            instance._resetImageLoader();
        }

        var config = [
            {type: 'settings', labelWidth: 50, labelAlign: 'right', inputWidth: 250},
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
            {type: 'template', name: '_file_loader', value: this.imageLoader, hidden: true},
            {type: 'container', name: 'labelsTemplates', inputWidth: 200, inputHeight: 300},
            {
                type: 'block', width: 300, blockOffset: 0, offsetTop: 80, list: [
                {type: 'button', name: 'image_load', value: '', className: 'image_loader'},
                {type: 'newcolumn'},
                {type: 'button', name: 'ok', value: 'Ok', offsetTop: 30, offsetLeft: 180}
            ]
            }
        ]
        var form = this.window.attachForm(config);
        initEvents(form);
        form.templates = {};
        return form;
    };

    BookletEditorComponent.prototype.populateData = function (entity) {
        this.controller.setEntity(entity);
        this._populateEntity(entity);
    };

    BookletEditorComponent.prototype.getData = function () {
        var instance = this;
        var customUpdater = {
            image: function (form, entity) {
                var $image = $('#booklet_item_image');
                var data = $image.data();
                if (data.isbase64) {
                    entity.image = $image.attr('src');
                }
            },
            listLabels: function (form, entity) {
                var notExcistingTypes = Object.keys(instance.parent.controller.labelTypes);
                for (var labelIndex = 0; labelIndex < entity.listLabels.length; labelIndex++) {
                    var label = entity.listLabels[labelIndex];
                    notExcistingTypes.splice(notExcistingTypes.indexOf(label.type), 1);
                    label.text = $('#' + label.type).val();
                }
                if (notExcistingTypes.length) {
                    for (var labelIndex = 0; labelIndex < notExcistingTypes.length; labelIndex++) {
                        var label = instance.parent.controller.bookletItemLabel.createNewEntity();
                        label.type = notExcistingTypes[labelIndex];
                        label.text = $('#' + notExcistingTypes[labelIndex]).val();
                        entity.listLabels.push(label);
                    }
                }
            }
        }
        var entity = this.controller.getEntity();
        Components.updateEntity(this.form, entity, customUpdater);
        return entity;
    };

    BookletEditorComponent.prototype.close = function () {
        this.window.close();
        this.clear();
    };

    BookletEditorComponent.prototype._populateEntity = function (entity) {
        var instance = this;
        var customUpdater = {
            image: function (form, entity) {
                if (entity.image && entity.image.length > 0) {
                    var $image = $('#booklet_item_image');
                    if (entity.image.indexOf('data:image') != 0) {
                        $image.attr('src', formatBookletImage(entity.image));
                    } else {
                        $image.data('isbase64', true);
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
                                instance.labelTemplates._select_mark(matches[1], true);
                                $('#' + label.type).val(label.text);
                            }
                        }
                    }
                }
            }
        }
        Components.updateFormData(this.form, entity, customUpdater);
    };

    BookletEditorComponent.prototype._updateImageTextareas = function (ids) {
        ids = Array.isArray(ids) ? ids : [ids];
        $('.text_fields_sub_container').hide();
        for (var idIndex = 0; idIndex < ids.length; idIndex++) {
            if (ids[idIndex] != '') {
                $('.text_fields_sub_container.' + ids[idIndex]).show();
            }
        }
    };

    BookletEditorComponent.prototype._initTemplates = function (form) {
        var instance = this;
        var formKey = 'image_inputs_templates';
        var editorFormDataView = new dhtmlXDataView({
            container: this.form.getContainer('labelsTemplates'),
            select: 'multiselect',
            type: {
                template: "<div><img src='#img#'/></div>",
                width: 60,
                height: 100
            }
        });
        $(editorFormDataView.$view).addClass('booklet_item_data_view');
        var templates = [
            {id: 'top_right', img: ''},
            {id: 'bottom_right', img: ''},
            {id: 'bottom_center', img: ''}
        ];

        for (var templateIndex = 0; templateIndex < templates.length; templateIndex++) {
            editorFormDataView.add(templates[templateIndex], 0);
        }
        editorFormDataView.attachEvent('onSelectChange', function (ids) {
            instance._updateImageTextareas(this.getSelected());
        })
        /*    editorFormDataView.attachEvent('onBeforeSelect', function(id) {
         if (!editorFormDataView.isSelected(id)) {
         editorFormDataView.select(id);
         } else {
         editorFormDataView.unselect(id);
         }
         })*/
        instance._updateImageTextareas([]);
        return editorFormDataView;
    };

    BookletEditorComponent.prototype._getFormData = function (form) {
        var formData = form.getFormData();
        $('#booklet_item_image_preview .booklet_item_image_label').each(
            function () {
                var thizz = $(this);
                formData[thizz.attr('id')] = thizz.val();
            }
        )
        return {};
    };

    BookletEditorComponent.prototype.clear = function () {
        var instance = this;
        if (this.form) {
            this.form.forEachItem(function (name) {
                var type = instance.form.getItemType(name);
                if (['input'].indexOf(type) >= 0 && name.indexOf('_') != 0) {
                    instance.form.setItemValue(name);
                }
            });
            $('#booklet_item_image_preview .booklet_item_image_label').val('');
            $('#booklet_item_image').attr('src', '').removeData('isbase64');
            this._resetImageLoader();
            this.labelTemplates.unselectAll();
        }
        this.controller.clear();
    };

    return BookletEditorComponent;
});
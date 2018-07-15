define(
    [
        'lodash',
        'common/services'
    ],
    function (_, Services) {
        'use strict';
        function getImageAbsolutePath(imageName) {
            return imageName;
        }

        return function (layout, list, cloud) {
            function prepareImage(image) {
                if (!image) {
                    return '';
                }
                if (form.imageParams.type === 'image-name') {
                    var imagesDirectory = adminSettings.categories_images_path || '';
                    return '/' + imagesDirectory + '/' + image;
                }
                return image;
            }

            function printImage(form) {
                return function (key, value) {
                    return [
                        '<div id="category-image" class="' + (form._isSelected ? 'active' : '') + '">',
                        form._isSelected && !value
                            ? '<span class="category-image-load-label">Нажми для загрузки изображения..</span>'
                            : '',
                        '<img src="' + prepareImage(value) + '">',
                        '</div>',
                        form._isSelected && value ? '<a id="category-image-clear" href="#">Очистить</a>' : ''
                    ].join('');
                };
            }

            function initImageLoader() {
                return '<input id="category-image-loader" style="display: none;" type="file" accept="image/*">';
            }

            function initForm() {
                var tabbar = layout.cells('b').attachTabbar();
                tabbar.addTab('a', 'Детали категории', null, null, true);
                var form = tabbar.cells('a').attachForm();
                var formConfig = [
                    {type: 'settings', position: 'label-left', labelWidth: 130, inputWidth: 300},
                    {
                        type: 'block', width: 1150,
                        list: [
                            {
                                type: 'block', name: 'main_info', width: 460, blockOffset: 20, offsetTop: 20,
                                list: [
                                    {type: 'input', name: 'id', label: 'ID', readonly: true},
                                    {type: 'input', name: 'key_item', label: 'Код'},
                                    {
                                        type: 'combo', name: 'parent_key', label: 'Родительский елемент',
                                        className: 'combo-override'
                                    },
                                    {
                                        type: 'input', name: 'order', label: 'Порядок',
                                        numberFormat: '000',
                                        note: {text: 'Меньшее значение в начале списка при отображении на сайте.'}
                                    },
                                    {type: 'input', name: 'value', label: 'Название', rows: 5},
                                    {
                                        type: 'template', name: 'image', label: 'Изображение:',
                                        format: printImage(form),
                                        note: {
                                            text: 'Не загружайти фотографии размером больше 100КБ.'
                                        }
                                    },
                                    {
                                        type: 'template', name: 'image-loader', format: initImageLoader
                                    }
                                ]
                            }
                        ]
                    }
                ];
                form.loadStruct(formConfig, 'json');
                form.getCombo('parent_key').allowFreeText(false);
                form.updateFormData = function (data) {
                    form._isSelected = true;
                    form._isNew = Boolean(data._isNew);
                    form.imageParams = {type: 'image-name'};
                    form.callEvent('onEntityLoaded', [data]);
                    form.setFormData({
                        id: data._isNew ? data._idLabel : data.id,
                        key_item: data.key_item,
                        order: data.order,
                        parent_key: data.parent_key,
                        value: data.value,
                        image: data._isNew ? data.image : getImageAbsolutePath(data.image)
                    });
                    initImageLoadAction(form);
                };

                form.lock();
                return form;
            }

            function setImageBase64(base64, params) {
                form.imageParams = {type: 'base64', params: params || {}};
                form.setItemValue('image', base64);
            }

            function setCloudImageBase64(base64, params) {
                form.imageParams = {type: 'cloud', params: params || {}};
                form.setItemValue('image', base64);
            }

            function initImageLoadAction() {
                $('#category-image').on('click', function () {
                    $('#category-image-loader').click();
                });
                $('#category-image-clear').on('click', function () {
                    form.imageParams = {};
                    form.setItemValue('image', '');
                    initImageLoadAction();
                });
                $('#category-image-loader').on('change', function () {
                    var file = document.querySelector('input[type=file]').files[0];
                    if (file.type.indexOf('image/') !== 0) {
                        return;
                    }
                    var reader = new FileReader();

                    reader.addEventListener('load', function () {
                        setImageBase64(reader.result);
                        form.setItemValue('image-loader');
                        initImageLoadAction();
                    }, false);

                    if (file) {
                        reader.readAsDataURL(file);
                    }

                });
            }

            function initEvents(form) {
                list.attachEvent('onSelectStateChanged', function (id) {
                    var entity = this.getUserData(id, 'entity');
                    form.unlock();
                    form.updateFormData(entity);
                    form._isSelected = true;
                });
                list.attachEvent('onSelectClear', function () {
                    var combo = form.getCombo('parent_key');
                    form._isSelected = false;
                    form.imageParams = {};
                    form.clear();
                    combo.unSelectOption();
                    form.lock();
                    form.lockImageLoader();
                });
                cloud.onAddToProduct(function (data) {
                    if (form._isSelected && data.length) {
                        var imageInfo = data[0];
                        setCloudImageBase64(imageInfo.preview.data, imageInfo.info);
                        initImageLoadAction();
                        cloud.hide();
                    }
                });
            }

            function initFormHelpers(form) {

                form.lockImageLoader = function lockImageLoader() {
                    form.setItemValue('image');
                };

                form.getEntity = function getEntity() {
                    if (!form._isSelected) {
                        return {};
                    }
                    var parentCombo = form.getCombo('parent_key');
                    var order = +form.getItemValue('order');
                    var entity = {
                        key_item: form.getItemValue('key_item'),
                        value: form.getItemValue('value'),
                        order: isNaN(order) ? 0 : order,
                        parent_key: parentCombo.getSelectedValue() || 'GN'
                    };

                    if (!form._isNew) {
                        entity.id = Number(form.getItemValue('id'));
                    }
                    if (form.imageParams.type === 'image-name') {
                        entity.image = form.getItemValue('image');
                        entity.image_params = {type: 'image-name'};
                    } else if (form.imageParams.type === 'base64') {
                        entity.image = form.getItemValue('image');
                        entity.image_params = {type: 'base64'};
                    } else if (form.imageParams.type === 'cloud') {
                        entity.image_params = form.imageParams;
                    }
                    return entity;
                };
            }

            function initParentKeys(form) {
                var categoriesList = [];

                function setCategoriesToParents(keyItem) {
                    var combo = form.getCombo('parent_key');
                    combo.clearAll();
                    combo.unSelectOption();
                    combo.addOption(categoriesList
                        .concat([{key_item: 'GN', value: 'Каталог'}])
                        .filter(function (category) {
                            return category.key_item !== keyItem;
                        })
                        .map(function (category) {
                            return [category.key_item, category.value + ' (' + category.key_item + ')'];
                        })
                    );
                }

                form.attachEvent('onEntityLoaded', function (entity) {
                    setCategoriesToParents(entity.key_item);
                });

                Services.getCategories()
                    .then(function (categories) {
                        categoriesList = categories;
                    });
            }

            function initSettings() {
                Services.getAdminSettings()
                    .then(function (settings) {
                        adminSettings = settings;
                    });
            }


            var adminSettings = {};
            var form = initForm();
            initSettings();
            initParentKeys(form);
            initEvents(form);
            initFormHelpers(form);
            return form;
        };
    }
);

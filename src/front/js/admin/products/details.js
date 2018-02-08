define([
    'lodash',
    'common/services',
    'common/components'
], function (_, Services, Components) {

    function initForm(tabbar) {
        tabbar.addTab('a', 'Детали товара', null, null, true);
        var form = tabbar.cells('a').attachForm();
        var formConfig = [
            {type: 'settings', position: 'label-left', labelWidth: 130, inputWidth: 300},
            {
                type: 'block', width: 1150,
                list: [
                    {
                        type: 'block', name: 'main_info', width: 460, blockOffset: 20, offsetTop: 20,
                        list: [
                            {type: 'input', name: 'id', label: 'Id', readonly: true},
                            {type: 'input', name: 'key_item', label: 'Код', readonly: true},
                            {type: 'input', name: 'name', label: 'Название', rows: 11}
                        ]
                    },
                    {type: 'newcolumn', offset: 50},
                    {
                        type: 'fieldset', width: 600, label: 'Категории', name: 'categories_1',
                        list: []
                    },
                    {type: 'newcolumn'},
                    {
                        type: 'fieldset',
                        width: 1200,
                        blockOffset: 0,
                        label: 'Описание',
                        name: 'description_keys',
                        list: []
                    }
                ]
            }
        ];
        form.loadStruct(formConfig, "json");
        form.updateFormData = function (data) {
            this.oldGoodCode = data['key_item'];
            var updater = {
                description: function (form, entity) {
                    var descriptions = (entity.description || '').split('|');
                    for (var descIndex = 0; descIndex < descriptions.length; descIndex++) {
                        var description = descriptions[descIndex].split('=');
                        form.setItemValue(description[0], description[1]);
                    }
                }
            };
            Components.updateFormData(this, data, updater);
            if (!data._isNew && U.hasContent(data.key_item)) {
                var radioId = data.key_item.substring(0, 2);
                form.checkItem('categories_items', radioId);
            }
            if (data._isNew) {
                form.setItemValue('id', data._idLabel);
                form.setItemValue('key_item', data._keyItemLabel);
            }

        };

        form.updateDescriptionConfig = function (data) {
            if (data) {
                var keysHeight = {
                    k_main: 14,
                    k_material: 5
                };
                var newColumn = true;
                for (var key in data) {
                    if (U.hasContent(data[key])) {
                        var item = {
                            type: 'input',
                            name: key,
                            label: data[key],
                            note: {text: key},
                            rows: keysHeight[key] || 5,
                            position: 'label-top',
                            inputWidth: 350,
                            labelWidth: 350
                        };
                        form.addItem('description_keys', {type: 'newcolumn', offset: 20});
                        form.addItem('description_keys', item);
                    }
                }
            }
        };

        form.attachEvent("onButtonClick", function (name) {
        });
        form.lock();
        reloadGoodsKeysTree(form);

        return form;
    }

    function reloadGoodsKeysTree(form) {
        Services.getGoodsKeys().then(
            /**
             * @typedef {{
             *      home_view: string,
             *      id: number,
             *      key_item: string,
             *      parent_key: string,
             *      value: string
             * }} Category
             * @param {Category[]} data
             */
            function (data) {
                form._au_categories_data = [];
                if (data.length) {
                    var allKeys = {};
                    var preparedData = normalizeCategories(data);
                    for (var dataIndex = 0; dataIndex < preparedData.length; dataIndex++) {
                        var entity = preparedData[dataIndex];
                        form._au_categories_data.push({code: entity.key_item, parent: entity.parent_key});
                        allKeys[entity.key_item] = allKeys[entity.key_item] || 0;
                        allKeys[entity.parent_key] = 1;
                        var value = _.map(entity.row, function(item, index, list) {
                            var categoryClassName = 'category-level category-' + index + ' ' + (index === list.length - 1 ? 'category-last' : '');
                            return '<span class="' + categoryClassName + '">' + item.value + '(' + item.key_item + ')</span>';
                        }).join(' > ');
                        form.addItem(
                            'categories_1',
                            {
                                type: 'radio', name: 'category', value: entity.key_item, label: value,
                                labelWidth: 380, inputWidth: 20, inputLeft: -10, position: 'label-right'
                            }
                        );
                    }
                }
            }
        );
    }

    /**
     * @typedef {{
             *      home_view: string,
             *      id: number,
             *      key_item: string,
             *      parent_key: string,
             *      value: string
             * }} Category
     * @param {Category[]} data
     */
    function normalizeCategories(data) {
        var childrenExists = {};
        var result = [];
        _.forEach(data, function (item) {
            childrenExists[item.key_item] = Boolean(_.filter(data, {parent_key: item.key_item}).length);
        });
        _.forEach(data, function (item) {
            if (!childrenExists[item.key_item]) {
                var categoryRow = [item];
                var parent = _.find(data, {key_item: item.parent_key});
                if (parent) {
                    categoryRow.unshift(parent);
                }
                var copy = _.cloneDeep(item);
                copy.row = categoryRow;
                result.push(copy);
            }
        });
        return result;
    }

    return {
        init: initForm
    };
});

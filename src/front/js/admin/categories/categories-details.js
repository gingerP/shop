define([
    'lodash',
    'common/services',
    'common/components',
    'common/dialog'
], function (_, Services, Components, Dialog) {

    return function (layout, list) {
        var tabbar = layout.cells('b').attachTabbar();
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
                            {type: 'input', name: 'key_item', label: 'Код', readonly: true},
                            {type: 'input', name: 'name', label: 'Название', rows: 11}
                        ]
                    }
                ]
            }
        ];
        form.loadStruct(formConfig, 'json');
        form.updateFormData = function (data) {
            this.oldGoodCode = data.key_item;
            if (!data._isNew && AuUtils.hasContent(data.key_item)) {
                var radioId = data.key_item.substring(0, 2);
                form.checkItem('categories_items', radioId);
            }
            if (data._isNew) {
                form.setItemValue('id', data._idLabel);
                form.setItemValue('key_item', data._keyItemLabel);
                form.setItemValue('key_item', data._keyItemLabel);
            }
        };

        form.lock();

        return form;
    };
});

define([
    'lodash',
    'common/services',
    'common/components'
], function (_, Services, Components) {

    return function initGrid(layout) {
        var tabbar = layout.cells('a').attachTabbar();
        tabbar.addTab('a', 'Категории', null, null, true);
        var grid = tabbar.tabs('a').attachGrid();
        grid.setImagePath('/src/front/dhtmlx/imgs/');
        grid.setHeader('#, Код, Название');
        grid.setInitWidths('40, 100,290');
        grid.setColAlign('left,left,left');
        grid.setColTypes('ro,ro,ro');
        grid.setColSorting('int,str,str');
        grid.init();

        grid.attachEvent('onBeforeSelect', function (id, oldId) {
            var oldEntity = this.getUserData(oldId, 'entity');
            if (_.isObject(oldEntity) && oldEntity._isNew === true) {
                return false;
            }
            return true;
        });

        grid.reloadGrid = function reloadGrid() {
            Services.getCategories()
                .then(function (categories) {
                    if (categories.length) {
                        for (var goodIndex = 0; goodIndex < categories.length; goodIndex++) {
                            var category = categories[goodIndex];
                            category._num = goodIndex + 1;
                            var array = Components.prepareEntityToGrid(category, ['id', 'key_item', 'value', 'image']);
                            var rowId = grid.addRow(category.id, array);
                            grid.setUserData(category.id, 'entity', category);
                            grid.setCellTextStyle(category.id, 0, 'color:#9e9e9e;');
                        }
                        grid.sortRows(0, 'int', 'asc');
                    }
                });
        };

        return grid;
    };
});

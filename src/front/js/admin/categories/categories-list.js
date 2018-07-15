define(
    [
        'lodash',
        'common/services',
        'common/components'
    ],
    function (_, Services, Components) {
        'use strict';
        return function initGrid(layout) {
            var tabbar = layout.cells('a').attachTabbar();
            tabbar.addTab('a', 'Категории', null, null, true);
            var grid = tabbar.tabs('a').attachGrid();
            grid.setImagePath('/src/front/dhtmlx/imgs/');
            grid.setHeader('ID, Код, Порядок, Название');
            grid.setInitWidths('40, 40, 60, 290');
            grid.setColAlign('left,left,left,left');
            grid.setColTypes('ro,ro,ro,ro');
            grid.setColSorting('int,int,str,str');
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
                        grid.clearAll();
                        if (categories.length) {
                            for (var goodIndex = 0; goodIndex < categories.length; goodIndex++) {
                                var category = categories[goodIndex];
                                category._num = goodIndex + 1;
                                var array = Components.prepareEntityToGrid(
                                    category,
                                    ['id', 'key_item', 'order', 'value']
                                );
                                grid.addRow(category.id, array);
                                grid.setUserData(category.id, 'entity', category);
                                grid.setCellTextStyle(category.id, 0, 'color:#9e9e9e;');
                            }
                            grid.sortRows(0, 'int', 'asc');
                        }
                    });
            };

            grid.reloadRow = function reloadRow(oldRowId, entity) {
                var index = grid.getRowIndex(oldRowId) + 1;
                entity._num = index;
                grid.changeRowId(oldRowId, entity.id);
                grid.setUserData(entity.id, 'entity', entity);
                Components.updateGridRow(grid, entity.id, entity, ['id', 'key_item', 'order', 'value']);
                grid.clearSelection();
                grid.selectRowById(entity.id);
            };

            return grid;
        };
    });

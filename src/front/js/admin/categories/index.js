require([
    'common/authorization',
    'common/authorization-view',
    'common/dialog',
    'common/service-entities',
    'common/services',
    'common/components',
    'categories/categories-list',
    'categories/categories-details',
    'categories/categories-toolbar'
], function (/**@type Authorization*/
             Authorization,
             AuthorizationView,
             Dialog, ServiceEntities, Services, Components,
             CategoriesList, CategoriesDetails, CategoriesToolbar) {

    function initLayout() {
        var layout = new dhtmlXLayoutObject({
            skin: Components.skin,
            parent: document.body,
            pattern: '2U',
            cells: [
                {id: 'a', text: 'Категории', width: 450},
                {id: 'b', text: 'Детали категории'}
            ]
        });
        layout.setOffsets({
            top: 0,
            right: 0,
            bottom: 0,
            left: 0
        });
        layout.cells('a').hideHeader();
        layout.detachHeader();
        return layout;
    }

    function getLoader() {
        return {
            reloadGrid: function (grid) {
                app.layout.progressOn();
                return Services.getGoods(null)
                    .then(function (goods) {
                        grid.clearSelection();
                        grid.clearAll();
                        app.form.clear();
                        app.serviceEntities.removeAll();
                        var gridState = {
                            hasSelection: false
                        };
                        app.toolbar.onStateChange(gridState);
                        if (goods.length) {
                            for (var goodIndex = 0; goodIndex < goods.length; goodIndex++) {
                                goods[goodIndex]._num = goodIndex + 1;
                                var array = Components.prepareEntityToGrid(goods[goodIndex], app.gridRowConfig);
                                var rowId = grid.addRow(goods[goodIndex].id, array);
                                grid.setUserData(goods[goodIndex].id, 'entity', goods[goodIndex]);
                                grid.setCellTextStyle(goods[goodIndex].id, 0, 'color:#9e9e9e;');
                            }
                            grid.sortRows(0, 'int', 'asc');
                        }
                        app.layout.progressOff();
                    }).catch(function (error) {
                        app.layout.progressOff();
                        Dialog.error(error);
                    });
            }
        };
    }

    function initServiceEntities() {
        var entity = {
            id: null,
            code: null,
            image: null,
            key_item: null,
            value: null
        };
        return new ServiceEntities().init(entity, 'id', 1);
    }

    function init() {
        AuUtils.dhtmlxDOMPreInit(document.documentElement, document.body);
        var layout = initLayout();
        Components.createMenu(layout);
        var serviceEntities = initServiceEntities();
        var categoriesList = CategoriesList(layout);
        var categoriesDetails = CategoriesDetails(layout);
        var toolbar = CategoriesToolbar(layout, categoriesList, categoriesDetails, serviceEntities);
        categoriesList.reloadGrid();

        categoriesList.attachEvent('onSelectStateChanged', function (id, oldId) {
            var entity = this.getUserData(id, 'entity');
            categoriesDetails.unlock();
            categoriesDetails.updateFormData(entity);
        });
    }

    Authorization.authorize()
        .then(init)
        .catch(function () {
            AuthorizationView.openLoginForm().then(init);
        });
});

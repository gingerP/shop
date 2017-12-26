require([
    'common/serviceEntities',
    'common/services',
    'common/components',
    'products/grid',
    'products/details',
    'products/images',
    'products/toolbar'
], function (ServiceEntities, Services, Components, Grid, Details, Images, Toolbar) {

    function initTabbar(layout) {
        return layout.cells('b').attachTabbar();
    }

    function initTreeGoodsKeys(form) {
        var tree = new dhtmlXTreeObject(form.getContainer('tree'), '100%', '100%', 'GN');
        tree.setImagePath(app.dhtmlxImgsPath + 'dhxtree_material/');
        app.loader.reloadGoodsKeysTree(tree);
        $(tree.allTree).attr('v-core-tree', 'goods_keys');
        tree.lock = function lockTree() {
            $('.tree-nav-keys').prop('disabled', true);
        };
        tree.unlock = function unlockTree() {
            $('.tree-nav-keys').prop('disabled', false);
        };
        tree.unselectAll = function unselectAll() {
            $('.tree-nav-keys').removeAttr('checked')
        };
        return tree;
    }

    function initLayout() {
        var layout = new dhtmlXLayoutObject({
            skin: Components.skin,
            parent: document.body,
            pattern: '2U',
            cells: [
                {id: 'a', text: 'Товары', width: 450},
                {id: 'b', text: 'Детали товара'}
            ]
        });
        layout.cells('a').hideHeader();
        layout.detachHeader();
        return layout;
    }

    function getLoader() {
        return {
            reloadGrid: function (grid) {
                Services.getGoods(null, function (goods) {
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
                });
            }
        };
    }

    function initServiceEntities() {
        var entity = {
            id: null,
            description: null,
            image_path: null,
            key_item: null,
            name: null,
            category: null
        };
        return new ServiceEntities().init(entity, 'id', 1);
    }

    function initGoodsOrder(callback) {
        require([
            '/src/front/js/admin/products/productsOrder.js'
        ], function (module) {
            app.goodsOrder = module;
            if (typeof(callback) === 'function') {
                callback();
            }
        })
    }

    (function init() {
        U.dhtmlxDOMPreInit(document.documentElement, document.body);
        app.loader = getLoader();
        app.layout = initLayout();
        app.tabbar = initTabbar(app.layout);
        app.menu = Components.createMenu(app.layout);
        app.grid = Grid.init(app.layout);
        app.form = Details.init(app.tabbar);
        app.images = Images.init(app.tabbar);
        app.serviceEntities = initServiceEntities();
        app.toolbar = Toolbar.init(app, app.layout, app.grid);
        initGoodsOrder();
        app.loader.reloadGrid(app.grid);
        Services.getDescriptionKeys(app.form.updateDescriptionConfig);
    })();
});
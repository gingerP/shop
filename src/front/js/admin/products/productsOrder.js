define([
    'common/dialog',
    'common/services'
], function (Dialog, Services) {
    var api;
    var win;
    var WIN_ID = 'goods-order-config';
    var dataView;
    var service = {
        save: function (data, callback) {
            Services.saveGoodsOrder(data)
                .then(function (result) {
                    callback(result);
                })
                .catch(Dialog.error);
        }
    };

    function convertGoodsWithOrder(goods) {
        if (goods && goods.length) {
            goods.sort(function (goodA, goodB) {
                if (goodA.good_index !== null && goodB.good_index !== null) {
                    return Number(goodA.good_index) > Number(goodB.good_index) ? 1 : -1;
                }
                return Number(goodA.id) > Number(goodB.id) ? 1 : -1;
            });
            goods.forEach(function (good, index) {
                good.good_index = index;
                good.good_id = good.id;
            });
            return goods;
        }
        return [];
    }

    function createWin() {
        var wins = new dhtmlXWindows();
        var size = getMaxSize();
        var win = wins.createWindow('w1', 20, 30, size.width, size.height);
        win.centerOnScreen();
        win.setModal(true);
        win.setMaxDimension(size.width, size.height);
        win.cell.className += ' ' + WIN_ID;
        win.setText('Настройка порядка отображения');
        initWinEvent(win);
        return win;
    }

    function createDataView(win) {
        var view = win.attachDataView({
            drag: true,
            select: true,
            type: {
                template: "\
                <div class='goods-order-item text-non-select'>\
                    <img src='#image_path#' class='goods-order-item-img' alt='#name#'>\
                    <div class='goods-order-item-label'>\
                        <div class='goods-order-item-index'>#good_index#</div>\
                        <div class='goods-order-item-name'>#name#</div>\
                    </div>\
                </div>",
                width: 132,
                height: 260
            }
        });
        view.$view.className += ' goods-order-data-view';
        view.refresh();
        initDataViewEvents(view);
        return view;
    }

    function createToolbar(win) {
        var toolbar = win.attachToolbar({
            icons_path: app.dhtmlxImgsPath
        });
        var input;
        toolbar.addButton('save', null, 'Сохранить');
        toolbar.addSeparator();
        toolbar.addInput('search_input', null);
        input = toolbar.getInput('search_input');
        if (input) {
            input.placeholder = 'Введите для поиска...';
            input.className += ' goods-order-search';
            $(input).on('input', function () {

            });
        }
        toolbar.attachEvent('onClick', function (id) {
            var orderData = updateOrder();
            service.save(orderData, function (result) {
                console.info('Save order: ' + result);
                renderGoods();
            });
        });
        toolbar.attachEvent('onValueChange', function (id) {
        });
        toolbar.setSkin('material');
        return toolbar;
    }

    function initDataViewEvents(view) {
        view.attachEvent('onBeforeDrag', function (context, ev) {
            var item = $('[dhx_f_id=' + context.start + ']');
            item.addClass('dragged');
            return true;
        });
        view.attachEvent('onBeforeDrop', function (context) {
            if (context.target === null) {
                var item = $('[dhx_f_id=' + context.start + ']');
                item.removeClass('dragged');
                return false;
            }
        });
        view.attachEvent('onAfterDrop', function (context, ev) {
            var item = $('[dhx_f_id=' + context.start + ']');
            item.removeClass('dragged');
            return true;
        });
    }

    function initWinEvent(win) {
        window.onresize = function () {
            var size = getMaxSize();
            win.setMaxDimension(size.width, size.height);
            win.setDimension(size.width, size.height);
            win.centerOnScreen();
        };

        win.attachEvent('onMaximize', function (win) {
            var size = getMaxSize();
            win.setMaxDimension(size.width, size.height);
            win.centerOnScreen();
        });
        win.attachEvent('onClose', function () {
            this.hide();
            this.setModal(false);
        });
        win.attachEvent('onShow', function () {
            this.setModal(true);
        });
    }

    function getMaxSize() {
        var ratio = 0.9;
        var w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
        var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
        return {
            width: w * ratio,
            height: h * ratio
        };
    }

    function init() {
        win = createWin();
        dataView = createDataView(win);
        toolbar = createToolbar(win);
        dataView.refresh();
        //fixed bug with toolbar visibility, need to update window size
        var size = getMaxSize();
        win.setMaxDimension(size.width, size.height);
        win.setDimension(size.width, size.height);
        win.centerOnScreen();
    }

    function show() {
        if (!win) {
            init();
        }
        win.show();
        renderGoods();
    }

    function renderGoods() {
        Services.getGoodsAdminOrder()
            .then(convertGoodsWithOrder)
            .then(function (goods) {
                for (var index = 0; index < goods.length; index++) {
                    goods[index].image_path += '?' + Date.now();
                }
                dataView.parse(goods, 'json');
                dataView.refresh();
            })
            .catch(Dialog.error);
    }

    function updateOrder() {
        var count = dataView.dataCount();
        var result = [];
        var index = 0;
        var id;
        for (; index < count; index++) {
            id = dataView.idByIndex(index);
            result.push({
                good_id: dataView.get(id).id,
                good_index: index
            });
        }
        return result;
    }

    function hide() {

    }

    api = {
        show: show,
        hide: hide
    };

    return api;
});

define([
    'common/services',
    'common/components'
], function (Services, Components) {

    function initToolbar(App, layout, grid) {

        var updater = {
            description: function (form, entity) {
                var formData = App.form.getFormData();
                var descriptions = [];
                for (var key in formData) {
                    if (key.indexOf('k_') == 0) {
                        descriptions.push(key + '=' + formData[key]);
                    }
                }
                entity.description = descriptions.join('|');
            }
        };

        function reloadRow(oldRowId, entity) {
            var index = App.grid.getRowIndex(oldRowId) + 1;
            entity._num = index;
            App.grid.changeRowId(oldRowId, entity.id);
            App.grid.setUserData(entity.id, 'entity', entity);
            Components.updateGridRow(App.grid, entity.id, entity, App.gridRowConfig);
            App.grid.clearSelection();
            App.grid.selectRowById(entity.id);
        }

        var handlers = {
            reload: function () {
                App.loader.reloadGrid(App.grid);
                App.form.lock();
                App.form.oldGoodCode = null;
                App.images.clearAll();
                App.images.lock(true);
            },
            add: function () {
                if (App.serviceEntities.canCreateNewEntity()) {
                    App.form.unlock();
                    App.form.oldGoodCode = null;
                    var entity = App.serviceEntities.createNewEntity();
                    var array = Components.prepareEntityToGrid(entity, App.gridRowConfig);
                    entity._idLabel = '<генерируется автоматически>';
                    entity._keyItemLabel = '<генерируется автоматически>';
                    grid.addRow(entity.id, array, 0);
                    grid.setUserData(entity.id, 'entity', entity);
                    App.form.clear();
                    App.images.clearAll();
                    App.images.lock(false);
                    grid.selectRowById(entity.id);
                }
            },
            save: function () {
                var rowId = App.grid.getSelectedRowId();
                var entity = App.grid.getUserData(rowId, 'entity');
                var oldRowId = rowId;
                Components.updateEntity(App.form, entity, updater);
                App.layout.progressOn();

                if (entity._isNew === true) {
                    delete entity.id;
                    delete entity.key_item;
                }

                Components.prepareEntity(entity);
                var id;
                return Services.updateGood(entity.id, entity)
                    .then(function callback(product) {
                        App.layout.progressOff();
                        id = product.id;
                        App.serviceEntities.removeAll();
                        dhtmlx.message({
                            text: 'Товар успешно сохранен.',
                            expire: 3000,
                            type: 'dhx-message-success'
                        });
                        return App.images.saveImages(product.id);
                    })
                    .then(function () {
                        return Services.getGood(id);
                    })
                    .then(function (product) {
                        reloadRow(oldRowId, product);
                    })
                    .catch(function () {
                        App.layout.progressOff();
                    });
            },
            saveOrder: function () {
                function show() {
                    App.goodsOrder.show();
                }

                if (!App.goodsOrder) {
                    initGoodsOrder(show);
                } else {
                    show();
                }
            },
            delete: function () {
                var name = App.form.getItemValue('name');
                if (!name) {
                    name = App.form.getItemValue('key_item');
                }
                dhtmlx.confirm({
                    title: "Удаление товара",
                    ok: "Да", cancel: "Отмена",
                    text: "Вы уверены что хотите удалить <br>'" + name + "' ?",
                    callback: function (result) {
                        if (result === true) {
                            var rowId = App.grid.getSelectedRowId();
                            var entity = App.grid.getUserData(rowId, 'entity');

                            function callback() {
                                App.grid.deleteRow(rowId);
                                App.serviceEntities.removeAll();
                                App.form.clear();
                                App.form.lock();
                                handlers.reload();
                            }

                            if (entity.hasOwnProperty('_isNew')) {
                                callback();
                            } else {
                                Services.deleteGood(entity.id).then(function (result) {
                                    if (result > 0) {
                                        callback();
                                    } else {
                                        console.info('nothing to delete!');
                                    }
                                });
                            }
                        }
                    }
                });
            },
            storage: function storage() {
                app.storage.open();
                app.storage.hideAddToProductButton();
            }
        };
        return Components.createToolbar(layout, handlers, [
            'reload', 'add', 'save', 'saveOrder', 'separator', 'delete', 'separator',
            {type: 'button', id: 'storage', text: 'Облако', img: 'storage.png', img_disabled: 'storage.png'}
        ]);
    }

    return {
        init: initToolbar
    };
});
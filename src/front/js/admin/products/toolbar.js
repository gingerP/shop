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
            saveDetails: function () {
                var rowId = App.grid.getSelectedRowId();
                var entity = App.grid.getUserData(rowId, 'entity');
                var oldRowId = rowId;
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
                    },
                    god_type: function (form, entity) {
                        var formData = App.form.getFormData();
                        entity.god_type = formData.god_type ? 'HARD' : 'SIMPLE';
                    }
                };
                Components.updateEntity(App.form, entity, updater);
                App.layout.progressOn();
                function reloadRow(entity) {
                    App.grid.changeRowId(oldRowId, entity.id);
                    App.grid.setUserData(entity.id, 'entity', entity);
                    Components.updateGridRow(App.grid, entity.id, entity, App.gridRowConfig);
                    App.grid.clearSelection();
                    App.grid.selectRowById(entity.id);
                }

                function callback(data) {
                    function callback() {
                        Services.getGood(data, reloadRow);
                        App.layout.progressOff();
                    }

                    App.serviceEntities.removeAll();
                }

                if (entity.hasOwnProperty('_isNew')) {
                    entity.id = null;
                    entity.key_item = null;
                }
                Components.prepareEntity(entity);
                Services.updateGood(entity.id, entity, callback);
            },
            save: function () {
                var rowId = App.grid.getSelectedRowId();
                var entity = App.grid.getUserData(rowId, 'entity');
                var oldRowId = rowId;
                Components.updateEntity(App.form, entity, updater);
                App.layout.progressOn();

                if (entity._isNew === true ) {
                    delete entity.id;
                    delete entity.key_item;
                }

                Components.prepareEntity(entity);
                Services.updateGood(entity.id, entity,
                    function callback(product) {
                        function reload() {
                            reloadRow(oldRowId, product);
                            App.layout.progressOff();
                        }
                        App.serviceEntities.removeAll();
                        dhtmlx.message({
                            text: 'Товар успешно сохранен.',
                            expire: 3000,
                            type: 'dhx-message-success'
                        });
                        App.images.saveImages(product.id, reload);
                    }
                );
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
                                Services.deleteGood(entity.id, function (result) {
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
            }
        };
        return Components.createToolbar(layout, handlers, ['reload', 'add', 'save', 'saveOrder', 'separator', 'delete']);
    }

    return {
        init: initToolbar
    };
});
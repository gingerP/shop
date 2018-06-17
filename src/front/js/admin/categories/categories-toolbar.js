define([
    'common/dialog',
    'common/services',
    'common/components'
], function (Dialog, Services, Components) {
    return function (layout, list, details, serviceEntities) {
        var handlers = {
            reload: function () {
                list.clearSelection();
                list.clearAll();
                list.reloadGrid();
                details.lock();
            },
            add: function () {
                if (serviceEntities.canCreateNewEntity()) {
                    details.unlock();
                    details.oldGoodCode = null;
                    var entity = serviceEntities.createNewEntity();
                    var array = Components.prepareEntityToGrid(entity, ['id', 'key_item', 'value', 'image']);
                    entity._idLabel = '<генерируется автоматически>';
                    list.addRow(entity.id, array, 0);
                    list.setUserData(entity.id, 'entity', entity);
                    details.clear();
                    list.selectRowById(entity.id);
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
                return Services.updateGood(entity)
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
                    .catch(function (error) {
                        App.layout.progressOff();
                        onError(error);
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
                                Services.deleteGood(entity.id)
                                    .then(function (result) {
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
        Components.createToolbar(layout, handlers, [
            'reload', 'add', 'save', 'delete', 'separator',
            {type: 'button', id: 'storage', text: 'Облако', img: 'storage.png', img_disabled: 'storage.png'}
        ]);
    };
});

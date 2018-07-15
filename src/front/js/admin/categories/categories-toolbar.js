define(
    [
        'common/dialog',
        'common/services',
        'common/components',
        'dropbox/dropbox'
    ],
    function (Dialog, Services, Components) {
        'use strict';
        return function (layout, list, details, cloud, serviceEntities) {

            function onError(error) {
                var message = 'Unknown error';
                if (typeof error === 'string') {
                    message = error;
                } else if (error.message) {
                    message = error.message;
                } else if (error.response && error.response.statusText) {
                    message = error.response.statusText;
                }
                dhtmlx.alert({
                    title: 'Alert',
                    type: 'alert-error',
                    text: '<span style="word-break: break-all">' + message + '</span>'
                });
                console.error(error);
            }

            var handlers = {
                reload: function () {
                    serviceEntities.removeAll();
                    list.callEvent('onSelectClear');
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
                    var oldRowId = list.getSelectedRowId();
                    if (!oldRowId) {
                        return null;
                    }
                    var entity = details.getEntity();
                    layout.progressOn();
                    return Services.saveCategory(entity)
                        .then(function callback(savedEntity) {
                            serviceEntities.removeAll();
                            layout.progressOff();
                            list.reloadRow(oldRowId, savedEntity);
                            layout.progressOff();
                            dhtmlx.message({
                                text: 'Категория успешно сохранена.',
                                expire: 3000,
                                type: 'dhx-message-success'
                            });
                        })
                        .catch(function (error) {
                            layout.progressOff();
                            onError(error);
                        });
                },
                delete: function () {
                    var oldRowId = list.getSelectedRowId();
                    if (!oldRowId) {
                        return;
                    }

                    var name = details.getItemValue('value');
                    if (!name) {
                        name = details.getItemValue('key_item');
                    }
                    if (!name) {
                        var oldEntity = list.getUserData(oldRowId, 'entity');
                        if (oldEntity._isNew) {
                            name = 'новый товар';
                        }
                    }
                    dhtmlx.confirm({
                        title: 'Удаление категории товаров',
                        ok: 'Да', cancel: 'Отмена',
                        text: 'Вы уверены что хотите удалить <br>"' + name + '" ?',
                        callback: function (result) {
                            if (result) {
                                var rowId = list.getSelectedRowId();
                                var entity = list.getUserData(rowId, 'entity');

                                if (entity.hasOwnProperty('_isNew')) {
                                    handlers.reload();
                                } else {
                                    Services.deleteCategory(entity.id)
                                        .then(function () {
                                            serviceEntities.removeAll();
                                            handlers.reload();
                                        });
                                }
                            }
                        }
                    });
                },
                storage: function storage() {
                    cloud.open();
                    cloud.showAddToProductButton();
                }
            };
            Components.createToolbar(layout, handlers, [
                'reload', 'add', 'save', 'delete', 'separator',
                {type: 'button', id: 'storage', text: 'Облако', img: 'storage.png', img_disabled: 'storage.png'}
            ]);
        };
    }
);

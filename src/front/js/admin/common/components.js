define([
    'common/authorization'
], function (/**@type Authorization*/
             Authorization) {
    if (typeof app === 'undefined') {
        app = {};
    }
    app.dhtmlxImgsPath = '/src/front/dhtmlx/imgs/';

    function initLogoutButton(menu, logoutBtnId) {
        var exitDOM = menu.idPull[menu.idPrefix + logoutBtnId];
        $('#' + exitDOM.id)
            .css({
                position: 'absolute',
                right: 0,
                'background-color': '#F04B4B',
                display: 'inline-block',
                cursor: 'pointer',
                color: '#fff',
                'font-weight': 'bold',
                'font-size': '12px',
                height: '32px',
                'line-height': '29px',
                padding: '2px 20px'
            });
    }

    function timeLabel(timeInSeconds) {
        var hours = Math.floor(timeInSeconds / (60 * 60));
        var hoursSeconds = hours * 60 * 60;
        var minutes = Math.floor((timeInSeconds - hoursSeconds) / 60);
        var seconds = Math.floor(timeInSeconds - (hoursSeconds + (minutes * 60)));
        return (hours < 10 ? '0' + hours : hours) + ':'
            + (minutes < 10 ? '0' + minutes : minutes) + ':'
            + (seconds < 10 ? '0' + seconds : seconds);
    }

    function initSessionTtlTimer(menu, sessionTtlBtnId) {
        var exitDOM = menu.idPull[menu.idPrefix + sessionTtlBtnId];
        $('#' + exitDOM.id).css(
            {right: '110px', position: 'absolute', color: 'rgb(240, 75, 75)', 'line-height': '32px'}
        );
        var time = Authorization.getSessionTtl();
        if (time) {
            menu.setItemText(sessionTtlBtnId, timeLabel(time - (Date.now() / 1000)));
            setInterval(function () {
                menu.setItemText(sessionTtlBtnId, timeLabel(time - (Date.now() / 1000)));
            }, 1000);
        }
    }

    var Components = {
        skin: 'material',
        reloadGrid: function (grid, list, columnKeys) {
            grid.clearAll();
            if (list.length) {
                for (var itemIndex = 0; itemIndex < list.length; itemIndex++) {
                    var array = Components.prepareEntityToGrid(list[itemIndex], columnKeys);
                    grid.addRow(list[itemIndex].id, array);
                    grid.setUserData(list[itemIndex].id, 'entity', list[itemIndex]);
                }
            }
        },
        confirm: function (text, callback) {
            dhtmlx.confirm({
                ok: 'Да',
                cancel: 'Отмена',
                text: text,
                callback: callback
            });
        },
        updateGridRow: function (grid, rowId, entity, keys, newRowId) {
            var rowData = Components.prepareEntityToGrid(entity, keys);
            grid.forEachCell(rowId, function (cellObj, colIndx) {
                cellObj.setValue(rowData[colIndx]);
            });
            if (AuUtils.hasContent(newRowId)) {
                grid.changeRowId(rowId, newRowId);
            }
        },

        updateFormData: function (form, entity, customUpdater) {
            if (form && entity) {
                for (var key in entity) {
                    if (key.indexOf('_') === 0) {
                        continue;
                    }
                    if (customUpdater && typeof customUpdater[key] === 'function') {
                        customUpdater[key](form, entity);
                    } else {
                        var type = form.getItemType(key);
                        switch (type) {
                            case 'radio':
                            case 'checkbox':
                                form.checkItem(key, entity[key]);
                                break;
                            case 'input':
                                form.setItemValue(key, entity[key]);
                                break;
                        }
                    }
                }
            }
        },


        updateEntity: function (form, entity, customUpdater) {
            if (form && entity) {
                for (var key in entity) {
                    if (customUpdater && typeof customUpdater[key] === 'function') {
                        customUpdater[key](form, entity);
                    } else {
                        var type = form.getItemType(key);
                        switch (type) {
                            case 'radio':
                            case 'checkbox':
                                entity[key] = form.getCheckedValue(key);
                                break;
                            case 'input':
                                entity[key] = form.getItemValue(key);
                                break;
                        }
                    }
                }
            }
        },

        prepareEntity: function (entity) {
            if (entity) {
                for (var key in entity) {
                    if (key.indexOf('_') === 0) {
                        delete entity[key];
                    } else if (entity[key] instanceof Array) {
                        for (var arrIndex = 0; arrIndex < entity[key].length; arrIndex++) {
                            Components.prepareEntity(entity[key][arrIndex]);
                        }
                    }
                }
            }

        },

        prepareEntityToGrid: function (entity, keys) {
            var row = [];
            for (var keyIndex = 0; keyIndex < keys.length; keyIndex++) {
                row.push(entity[keys[keyIndex]]);
            }
            return row;
        },

        createMenu: function (layout) {
            var exitKey = 'logout';
            var sessionTtl = 'session-ttl';
            var menu = layout.attachMenu({
                parent: 'menu',
                image_path: '/src/front/dhtmlx/imgs/',
                items: [
                    {id: 'settings', text: 'Настройки', enabled: true},
                    {id: 'products', text: 'Товары', enabled: true},
                    {id: 'booklets', text: 'Буклеты', enabled: true},
                    {id: 'cloud', text: 'Облако', enabled: true},
                    {id: 'tree', text: 'Дерево навигации', disabled: true},
                    {id: 'prices', text: 'Прайс-листы', disabled: true},
                    {id: 'contacts', text: 'Контакты', disabled: true},
                    {id: sessionTtl, text: 'Время сессии'},
                    {id: exitKey, text: 'Выход', img: '/images/icons/exit.png'}
                ]
            });
            menu.attachEvent('onClick', function (id, zoneId, cas) {
                if (id === exitKey) {
                    Authorization.logout();
                    return;
                }
                if (id === sessionTtl) {
                    return;
                }
                window.open(document.location.origin + '/admin/' + id, '_self');
            });
            initLogoutButton(menu, exitKey);
            initSessionTtlTimer(menu, sessionTtl);

            return menu;
        },

        createToolbar: function (layout, handlers, buttons) {
            var config = [
                {type: 'button', id: 'reload', text: 'Обновить', img: 'reload.png', img_disabled: 'reload_dis.png'},
                {type: 'button', id: 'add', text: 'Добавить', img: 'add.png', img_disabled: 'add_dis.png'},
                {type: 'button', id: 'save', text: 'Сохранить', img: 'save.png', img_disabled: 'save_dis.png'},
                {type: 'separator'},
                {type: 'button', id: 'delete', text: 'Удалить', img: 'delete.png', img_disabled: 'delete_dis.png'}
            ];
            if (AuUtils.hasContent(buttons)) {
                config = [];
                var _buttons = {
                    reload: {
                        type: 'button',
                        id: 'reload',
                        text: 'Обновить',
                        img: 'reload.png',
                        img_disabled: 'reload.png'
                    },
                    add: {type: 'button', id: 'add', text: 'Добавить', img: 'add.png', img_disabled: 'add_dis.png'},
                    save: {
                        type: 'button',
                        id: 'save',
                        text: 'Сохранить',
                        img: 'save.png',
                        img_disabled: 'save_dis.png'
                    },
                    delete: {
                        type: 'button',
                        id: 'delete',
                        text: 'Удалить',
                        img: 'delete.png',
                        img_disabled: 'delete_dis.png'
                    },
                    loadBackground: {type: 'button', id: 'loadBackground', text: 'Загрузить фон'},
                    saveOrder: {
                        type: 'button',
                        id: 'saveOrder',
                        text: 'Настроить порядок',
                        img: 'settings.png',
                        img_disabled: 'settings_dis.png'
                    },
                    separator: {type: 'separator'},
                    preview: {type: 'button', id: 'preview', text: 'Просмотр'}
                };
                for (var btnIndex = 0; btnIndex < buttons.length; btnIndex++) {
                    var button = buttons[btnIndex];
                    if (typeof button === 'string') {
                        config.push(_buttons[button]);
                    } else if (typeof button === 'object') {
                        config.push(button);
                    }
                }
            }
            var toolbar = layout.attachToolbar({
                icon_path: '/images/icons/',
                items: config
            });
            if (typeof handlers !== 'undefined') {
                toolbar.customHandlers = handlers;
                toolbar.attachEvent('onClick', function (id) {
                    if (this.customHandlers.hasOwnProperty(id) && typeof this.customHandlers[id] === 'function') {
                        return this.customHandlers[id]();
                    }
                });
            }
            toolbar.onStateChange = function (state) {
                var thiz = this;
                this.forEachItem(function (itemId) {
                    if (state.items && state.items[itemId]) {
                        thiz[state.items[itemId].enableState ? 'enableItem' : 'disableItem'](itemId);
                    } else {
                        switch (itemId) {
                            case 'save':
                            case 'delete':
                                thiz[state.hasSelection === true ? 'enableItem' : 'disableItem'](itemId);
                                break;
                            case 'reload':
                            case 'loadBackground':
                            case 'preview':
                            case 'add':
                                thiz.enableItem(itemId);
                                break;
                        }
                    }
                });
                if (state.hasSelection) {

                } else {

                }
            };
            return toolbar;
        },

        initDhtmlxWindow: function (options, closeCallback) {
            var myWins = new dhtmlXWindows({
                image_path: '/src/front/dhtmlx/imgs/',
                skin: 'dhx_blue'
            });
            var _options = {
                id: AuUtils.getRandomString(),
                left: 20,
                top: 30,
                width: 300,
                height: 200,
                center: true,
                caption: 'Title'
            };
            $.extend(true, _options, options || {});
            var win = myWins.createWindow(_options);
            win.attachEvent('onClose', function () {
                if (typeof closeCallback === 'function') {
                    closeCallback();
                }
                this.hide();
            });
            return win;
        }
    };
    return Components;
});

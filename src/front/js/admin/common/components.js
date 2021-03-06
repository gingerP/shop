define([], function() {
    if (typeof app == 'undefined') {
        app = {};
    }
    app.dhtmlxImgsPath = '/src/front/dhtmlx/imgs/';

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
                ok: "Да",
                cancel: "Отмена",
                text: text,
                callback: callback
            });
        },
        updateGridRow: function (grid, rowId, entity, keys, newRowId) {
            var rowData = Components.prepareEntityToGrid(entity, keys);
            grid.forEachCell(rowId, function (cellObj, colIndx) {
                cellObj.setValue(rowData[colIndx]);
            });
            if (U.hasContent(newRowId)) {
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
                    if (key.indexOf('_') == 0) {
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
            var menu = layout.attachMenu({
                parent: 'menu',
                image_path: '/src/front/dhtmlx/imgs/',
                items: [
                    {id: "goods", text: "Товары"},
                    {id: "tree", text: "Дерево навигации", disabled: true},
                    {id: "prices", text: "Прайс-листы"},
                    {id: "contacts", text: "Контакты"},
                    {id: "booklets", text: "Буклеты"},
                    {id: exitKey, text: "Выход"}
                ]
            });
            menu.attachEvent("onClick", function (id, zoneId, cas) {
                window.open(document.location.origin + "/admin/" + id, "_self");
            });
            var exitDOM = menu.idPull[menu.idPrefix + exitKey];
            $("#" + exitDOM.id).css("position", "absolute").css("right", "0").css("background-color", "#F04B4B").css("display", "inline-block").css("cursor", "pointer").css("color", "#fff").css("font-weight", "bold").css("font-size", "12px").css("border-radius", "3px").css("box-shadow", "0px 0px 3px rgba(255, 255, 255, 0.5) inset").css("border", "1px solid #F04B4B");

            return menu;
        },

        createToolbar: function (layout, handlers, buttons, cellName) {
            var config = [
                {type: "button", id: "reload", text: "Обновить"},
                {type: "button", id: "add", text: "Добавить"},
                {type: "button", id: "save", text: "Сохранить"},
                {type: "separator"},
                {type: "button", id: "delete", text: "Удалить"}
            ];
            if (U.hasContent(buttons)) {
                var config = [];
                var _buttons = {
                    reload: {type: "button", id: "reload", text: "Обновить"},
                    add: {type: "button", id: "add", text: "Добавить"},
                    save: {type: "button", id: "save", text: "Сохранить"},
                    delete: {type: "button", id: "delete", text: "Удалить"},
                    loadBackground: {type: "button", id: "loadBackground", text: "Загрузить фон"},
                    saveOrder: {type: "button", id: "saveOrder", text: "Настроить порядок"},
                    separator: {type: "separator"},
                    preview: {type: "button", id: "preview", text: "Просмотр"}
                };
                for (var btnIndex = 0; btnIndex < buttons.length; btnIndex++) {
                    config.push(_buttons[buttons[btnIndex]]);
                }
            }
            var toolbar = layout.cells(cellName || "a").attachToolbar({items: config});
            if (typeof handlers != 'undefined') {
                toolbar.customHandlers = handlers;
                toolbar.attachEvent("onClick", function (id) {
                    if (this.customHandlers.hasOwnProperty(id) && typeof this.customHandlers[id] == 'function') {
                        this.customHandlers[id]();
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

        initDhtmlxWindow: function (vars, closeCallback) {
            var myWins = new dhtmlXWindows({
                image_path: '/src/front/dhtmlx/imgs/',
                skin: "dhx_blue"
            });
            var _vars = {
                id: U.getRandomString(),
                left: 20,
                top: 30,
                width: 300,
                height: 200,
                center: true,
            };
            $.extend(true, _vars, vars || {});
            var win = myWins.createWindow(_vars);
            win.attachEvent('onClose', function () {
                if (typeof closeCallback == 'function') {
                    closeCallback();
                }
                this.hide();
            });
            return win;
        }
    };
    return Components;
});

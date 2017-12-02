if (typeof app == 'undefined') {
    app = {};
}
app.dhtmlxImgsPath = '/src/front/dhtmlx/imgs/';

components = {
    skin: 'material',
    reloadGrid: function(grid, list, columnKeys) {
        grid.clearAll();
        if (list.length) {
            for (var itemIndex = 0; itemIndex < list.length; itemIndex++) {
                var array = components.prepareEntityToGrid(list[itemIndex], columnKeys);
                grid.addRow(list[itemIndex].id, array);
                grid.setUserData(list[itemIndex].id, 'entity', list[itemIndex]);
            }
        }
    },
    confirm: function(text, callback) {
        dhtmlx.confirm({
            ok: "Да",
            cancel: "Отмена",
            text: text,
            callback: callback
        });
    },
    updateGridRow: function(grid, rowId, entity, keys, newRowId) {
        var rowData = components.prepareEntityToGrid(entity, keys);
        grid.forEachCell(rowId, function(cellObj, colIndx) {
            cellObj.setValue(rowData[colIndx]);
        });
        if (U.hasContent(newRowId)) {
            grid.changeRowId(rowId, newRowId);
        }
    },

    updateFormData: function(form, entity, customUpdater) {
        if (form && entity) {
            for (var key in entity) {
                if (key.indexOf('_') == 0) {
                    continue;
                }
                if (customUpdater && typeof customUpdater[key] == 'function') {
                    customUpdater[key](form, entity);
                } else if (form.isItem(key)) {
                    form.setItemValue(key, entity[key]);
                }
            }
        }
    },


    updateEntity: function(form, entity, customUpdater) {
        if (form && entity) {
            for (var key in entity) {
                if (customUpdater && typeof customUpdater[key] == 'function') {
                    customUpdater[key](form, entity);
                } else if (form.isItem(key)) {
                    entity[key] = form.getItemValue(key);
                }
            }
        }
    },

    prepareEntity: function(entity) {
        if (entity) {
            for(var key in entity) {
                if (key.indexOf('_') == 0) {
                    delete entity[key];
                } else if (entity[key] instanceof Array) {
                    for(var arrIndex = 0; arrIndex < entity[key].length; arrIndex++) {
                        components.prepareEntity(entity[key][arrIndex]);
                    }
                }
            }
        }

    },

    prepareEntityToGrid: function(entity, keys) {
        var row = [];
        for (var keyIndex = 0; keyIndex < keys.length; keyIndex++) {
            row.push(entity[keys[keyIndex]]);
        }
        return row;
    },

    createMenu: function(layout) {
        var exitKey = 'logout';
        var menu = layout.attachMenu({
            parent:'menu',
            image_path:'/src/front/dhtmlx/imgs/',
            items:[
                {id: "goods", text:"Товары"},
                {id: "tree", text:"Дерево навигации", disabled: true},
                {id: "prices", text:"Прайс-листы"},
                {id: "contacts", text:"Контакты"},
                {id: "booklets", text:"Буклеты"},
                {id: exitKey, text: "Выход"}
            ]
        });
        menu.attachEvent("onClick", function(id, zoneId, cas) {
            window.open(document.location.origin + "/admin/" + id,"_self");
        });
        var exitDOM = menu.idPull[menu.idPrefix + exitKey];
        $("#" + exitDOM.id).
            css("position", "absolute").
            css("right", "0").
            css("background-color", "#F04B4B").
            css("display", "inline-block").
            css("cursor", "pointer").
            css("color", "#fff").
            css("font-weight", "bold").
            css("font-size", "12px").
            css("border-radius", "3px").
            css("box-shadow", "0px 0px 3px rgba(255, 255, 255, 0.5) inset").
            css("border", "1px solid #F04B4B");

        return menu;
    },

    createToolbar: function(layout, handlers, buttons, cellName) {
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
            toolbar.attachEvent("onClick", function(id){
                if (this.customHandlers.hasOwnProperty(id) && typeof this.customHandlers[id] == 'function') {
                    this.customHandlers[id]();
                }
            });
        }
        toolbar.onStateChange = function(state) {
            var thiz = this;
            this.forEachItem(function(itemId) {
                if (state.items && state.items[itemId]) {
                    thiz[state.items[itemId].enableState? 'enableItem': 'disableItem'](itemId);
                } else {
                    switch (itemId) {
                        case 'save':
                        case 'delete':
                            thiz[state.hasSelection === true? 'enableItem': 'disableItem'](itemId);
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
            skin:"dhx_blue"
        });
        var _vars = {id: U.getRandomString(),
            left: 20,
            top: 30,
            width: 300,
            height: 200,
            center: true,
        };
        $.extend(true, _vars, vars || {});
        var win = myWins.createWindow(_vars);
        win.attachEvent('onClose', function() {
            if (typeof closeCallback == 'function') {
                closeCallback();
            }
            this.hide();
        });
        return win;
    }
};


Handlers = function(callback){
    this.callback = callback;
};

Handlers.prototype.success = function(data) {
    if (typeof(this.callback) == 'function') {
        this.callback(data);
    }
};

serviceWrapper = (function() {
    function load(apiMethod, params, handlers) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: params,
            url: '/api/' + apiMethod,
            context: document.body
        }).done(function(data) {
                if (typeof(handlers.success) == 'function') {
                    handlers.success(data);
                }
            }
        ).fail(function()  {
            alert("Sorry. Server unavailable. ");
        });;
    }
    return {
        getAddresses: function(id, callback) {
            load('getAddresses', {id: -1}, new Handlers(callback));
        },
        getGoods: function(id, callback) {
            load('getGoods', {id: -1}, new Handlers(callback));
        },
        saveGoodsOrder: function(data, callback) {
            load('saveOrder', data, new Handlers(callback));
        },
        getNextGoodCode: function(code, callback) {
            load('getNextGoodCode', {code: code}, new Handlers(callback));
        },
        getDescriptionKeys: function(callback) {
            load('getDescriptionKeys', null, new Handlers(callback))
        },
        getPrices: function(callback) {
            load('getPrices', null, new Handlers(callback));
        },
        getGoodsKeys: function(callback) {
            load('getGoodsKeys', null, new Handlers(callback));
        },
        updateGood: function(id, data, callback) {
            load('updateGood', {id: id, data: data}, new Handlers(callback));
        },
        getGood: function(id, callback) {
            load('getGood', {id: id}, new Handlers(callback));
        },
        getGoodsAdminOrder: function(callback) {
            load('getAdminOrder', null, new Handlers(callback));
        },
        getGoodImages: function(id, callback) {
            load('getGoodImages', {id: id}, new Handlers(callback));
        },
        deleteGood: function(id, callback) {
            load('deleteGood', {id: id}, new Handlers(callback));
        },
        uploadImagesForGood: function(id, oldGoodKey, data, callback) {
            load('uploadImagesForGood', {id: id, old_good_key: oldGoodKey, data: data}, new Handlers(callback));
        },
        updatePrices: function(data, callback) {
            load('updatePrices', {data: data}, new Handlers(callback));
        },
        /*****************************************Booklets*************************************/
        listBooklets: function(mapping, callback) {
            load('listBooklets', {mapping: mapping}, new Handlers(callback));
        },
        getBooklet: function(id, mapping, callback) {
            load('getBooklet', {id: id, mapping: mapping}, new Handlers(callback));
        },
        saveBooklet: function(data, callback) {
            load('saveBooklet', {data: data}, new Handlers(callback));
        },
        deleteBooklet: function(id, callback) {
            load('deleteBooklet', {id: id}, new Handlers(callback));
        },
        getBookletBackgrounds: function(callback) {
            load('getBookletBackgrounds', null, new Handlers(callback));
        }
    }
})();

ServiceEntities = function() {};

ServiceEntities.prototype.init = function(entity, entityIdKey, maxEntitiesCount, defaults) {
    this.defaults = defaults || {};
    this.maxEntitiesCount = maxEntitiesCount;
    this.newEntitiesCount = 0;
    this.newEntities = {};
    this.idField = entityIdKey;
    this.entity = entity;
    return this;
};

ServiceEntities.prototype.createNewEntity = function() {
    var newEntity = JSON.parse(JSON.stringify(this.entity));
    this.newEntitiesCount++;
    newEntity[this.idField] = this.generateId(this.newEntitiesCount);
    this.newEntities[newEntity[this.idField]] = newEntity;
    newEntity._isNew = true;
    for(var key in this.defaults) {
        if (newEntity.hasOwnProperty(key)) {
            newEntity[key] = typeof this.defaults[key] == 'function'? this.defaults[key](): this.defaults[key];
        }
    }
    return newEntity;
};

ServiceEntities.prototype.removeEntity = function(id) {
    delete this.newEntities[id];
    return this;
};

ServiceEntities.prototype.removeAll = function() {
    this.newEntities = {};
    this.newEntitiesCount = 0;
};

ServiceEntities.prototype.canCreateNewEntity = function() {
    return this.maxEntitiesCount > this.newEntitiesCount;
};

ServiceEntities.prototype.generateId = function(id) {
  return  '_new:' + id;
};

/**********************************************************************************/

Observable = function() {
    this.listeners = {};
}

Observable.prototype.addListener = function(propertyName, listener) {
    this.listeners[propertyName] = this.listeners[propertyName] || [];
    this.listeners[propertyName].push(listener);
    return this;
};

Observable.prototype.propertyChange = function(propertyName, state, owner) {
    if (typeof propertyName == 'string' && propertyName.length > 1 && this.listeners[propertyName].length) {
        for (var listenerIndex = 0; listenerIndex < this.listeners[propertyName].length; listenerIndex++) {
            var listenerFunction = this.listeners[propertyName][listenerIndex]['on' + propertyName[0].toUpperCase() + propertyName.substr(1, propertyName.length) + 'Change'];
            if (typeof listenerFunction == 'function') {
                listenerFunction(state, propertyName, owner);
            }
        }
    }
    return this;
};
$(document).ready(function() {
    U.dhtmlxDOMPreInit(document.documentElement, document.body);
    app = {};
    createPage();
})


function createPage() {
    init();
};

function init() {
    app.layout = initLayout();
    app.serviceEntities = initServiceEntities();
    app.menu = components.createMenu(app.layout);
    app.toolbar = initToolbar();
    app.grid = initGrid();
    app.entities = {};
    loader.reloadGrid(app.grid);
}

function initServiceEntities() {
    var entity = {
        id: null,
        name: null,
        new_name: null,
        file: null,
        action: null,
        size: "--",
        modification_time: "--"
    }
    return new ServiceEntities().init(entity, 'id', 999);
}

function initLayout() {
    var layout = new dhtmlXLayoutObject({
        parent: document.body,
        pattern: "1C",
        cells: [
            {id: "a", text: "Прайс-листы"}
        ]
    });
    layout.cells("a").hideHeader();
    layout.detachHeader();
    return layout;
}

function initToolbar() {
    var $loader = $('#load_price');
    $loader.on('change', function() {
        if (this.files.length && /(\.xls|\.xlsx){1}$/.test(this.files[0].name)) {
            var file = this.files[0];
            if (app.grid.hasFile(file.name)) {
                alert('Файл "' + file.name + '" уже существует, переименуйте его!');
                return;
            }
            var reader = new FileReader();
            reader.onload = function(e) {
                var entity = app.serviceEntities.createNewEntity();
                entity.action = 'add';
                entity.name = file.name;
                entity.file = reader.result;
                app.entities[entity.id] = entity;
                var array = components.prepareEntityToGrid(entity, ["name", "size", "modification_time"]);
                app.grid.addRow(entity.id, array);
                app.grid.setUserData(entity.id, 'entity', entity);
                app.grid.selectRowById(entity.id, false, true, true);
                $loader.val("");
            }
            reader.readAsDataURL(file);
        }
    });
    var handlers = {
        reload: function() {
            var hasNewPrices = false;
            for (var key in app.entities) {
                if (app.entities[key].action == 'add') {
                    hasNewPrices = true;
                    break;
                }
            }
            function callback() {
                app.entities = {};
                loader.reloadGrid(app.grid);
            }
            if (hasNewPrices) {
                dhtmlx.confirm({
                    title:"Обновление",
                    ok:"Всеравно обновить", cancel:"Отмена",
                    text:"Перед обновлением сохраните новые прайс-листы, чтобы не потерять их.",
                    callback: function(result) {
                        if (result === true) {
                            callback();
                        }
                    }
                })
            } else {
                callback();
            }
        },
        add: function () {
            if (app.serviceEntities.canCreateNewEntity()) {
                $loader.trigger('click');
            }
        },
        delete: function() {
            var rowId = app.grid.getSelectedRowId();
            var entity = app.grid.getUserData(rowId, 'entity');
            dhtmlx.confirm({
                title:"Удаление прайс-листа",
                ok:"Да", cancel:"Отмена",
                text:"Вы уверены что хотите удалить <br>'" + entity.name + "' ?",
                callback: function(result) {
                    if (result === true) {
                        app.entities[rowId].action = 'del';
                        app.grid.deleteRow(rowId);
                        app.serviceEntities.removeEntity(rowId);
                    }
                }
            })
        },
        save: function() {
            serviceWrapper.updatePrices(app.entities, function() {
                app.entities = {};
                app.serviceEntities.removeAll();
                loader.reloadGrid(app.grid);
            })
        }
    };
    return components.createToolbar(app.layout, handlers);
}

function initGrid() {
    var grid = app.layout.cells("a").attachGrid();
    grid.setImagePath("../../../codebase/imgs/");
    grid.setHeader("Название, Размер, Дата последнего редактирования");
    grid.setInitWidths("*,100,300");
    grid.setColAlign("left,left,left");
    grid.setColTypes("ed,ro,ro");
    grid.setColSorting("str,str,str");
    grid.enableAutoWidth(true);
    grid.hasFile = function(fileName) {
        var result = false;
        this.forEachRow(function(id){
            var entity = app.entities[id];
            var name = entity.new_name || entity.name;
            if (fileName == name) {
                result = true;
                return false;
            }
        });
        return result;
    }
    grid.handleSameNames = function(sameNamesCallback) {
        sameNamesCallback = sameNamesCallback || function(grid, id) {
            grid.setRowColor(id, '#FC9898');
        };
        function clearCallback(grid, id) {
            grid.setRowColor(id, '#FFFFFF');
        }
        var result  = {};
        var result_ = {};
        this.forEachRow(function(id){
            clearCallback(this, id);
            var entity = app.entities[id];
            var name = encodeURIComponent(entity.new_name || entity.name);
            if (result_.hasOwnProperty(name)) {
                sameNamesCallback(this, !result.hasOwnProperty(name)? result_[name]: result[name]);
                result[name] = id;
            }
            result_[name] = id;
        });
        for (var key in result) {
            sameNamesCallback(this, result[key]);
        }
        return Object.keys(result).length > 0;
    }

    grid.init();
    grid.attachEvent("onSelectStateChanged", function(id){
        var entity = this.getUserData(id, 'entity');
        //app.form.unlock();
        var gridState = {
            hasSelection: true
        }
        app.toolbar.onStateChange(gridState);
        //app.form.updateFormData(entity);
    });
    grid.attachEvent("onEditCell", function(stage,rId,cInd,nValue,oValue){
        if (stage === 2 && nValue != oValue && app.entities[rId] && app.entities[rId].name != nValue) {
            if (U.hasContent(app.entities[rId].file)) {
                app.entities[rId].name = nValue;
            } else {
                app.entities[rId].new_name = nValue;
                app.entities[rId].action = 'rename';
            }
        }
        return true;
    });
    return grid;
}

var loader = {
    reloadGrid: function(grid) {
        grid.clearSelection();
        grid.clearAll();
        app.serviceEntities.removeAll();
        var gridState = {
            hasSelection: false
        }
        app.toolbar.onStateChange(gridState);
        serviceWrapper.getPrices(function(prices) {
            if (prices.length) {
                for (var priceIndex = 0; priceIndex < prices.length; priceIndex++) {
                    var id = U.getRandomString();
                    var creatingDate = new Date(prices[priceIndex].modification_time);
                    prices[priceIndex].modification_time = moment(creatingDate).format('YYYY-MM-DD hh:mm:ss');
                    var entity = components.prepareEntityToGrid(prices[priceIndex], ["name", "size", "modification_time"]);
                    var entity_ = app.serviceEntities.createNewEntity();
                    $.extend(true, entity_, prices[priceIndex]);
                    app.entities[id] = entity_;
                    grid.addRow(id, entity);
                    grid.setUserData(id, 'entity', prices[priceIndex]);
                }
                grid.sortRows(0,"int","asc");
            }
        });
    }
}
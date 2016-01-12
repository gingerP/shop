$(document).ready(function() {
    U.dhtmlxDOMPreInit(document.documentElement, document.body);
    createPage();
})


function createPage() {
    init();
};

function init() {
    app.layout = initLayout();
    app.tabbar = initTabbar(app.layout);
    app.menu = components.createMenu(app.layout);
    app.grid = initGrid(app.layout);
    app.form = initForm(app.tabbar);
    app.treeGoodsKeys = initTreeGoodsKeys(app.form);
    app.images = initImages(app.tabbar);
    app.serviceEntities = initServiceEntities();
    app.toolbar = initToolbar(app.layout, app.grid);
    loader.reloadGrid(app.grid);
    serviceWrapper.getDescriptionKeys(app.form.updateDescriptionConfig);
}

function initLayout() {
    var layout = new dhtmlXLayoutObject({
        parent: document.body,
        pattern: "2U",
        cells: [
            {id: "a", text: "Товары", width: 450},
            {id: "b", text: "Детали товара"}
        ]
    });
    layout.cells("a").hideHeader();
    layout.detachHeader();
    return layout;
}

function initTabbar(layout) {
    return layout.cells('b').attachTabbar();
}

function initGrid(layout) {
    var grid = layout.cells("a").attachGrid();
    grid.setImagePath(app.dhtmlxImgsPath);
    grid.setHeader("ID, Код, Название, Дерево");
    grid.setInitWidths("40,100,290,200");
    grid.setColAlign("left,left,left,left");
    grid.setColTypes("ro,ro,ro,ro");
    grid.setColSorting("int,str,str,str");
    grid.init();

    grid.attachEvent("onSelectStateChanged", function(id){
        var entity = this.getUserData(id, 'entity');
        app.form.unlock();
        var gridState = {
            hasSelection: true
        }
        app.toolbar.onStateChange(gridState);
        app.form.updateFormData(entity);
        app.images.loadImages(entity.id);
    });

    return grid;
}

function initForm(tabbar) {
    tabbar.addTab('a', 'Детали товара', null, null, true);
    var form = tabbar.cells('a').attachForm();
    var formConfig = [
        {type: "settings", position: "label-left", labelWidth: 130, inputWidth: 300},
        {type: 'block', width: 1150, list: [
            {type: 'block', name: 'main_info', width: 500, list: [
                {type: 'input', name: 'id', label: 'Номер', readonly: true},
                {type: 'block', width: 600, blockOffset: 0, list: [
                    {type: 'input', name: 'key_item', label: 'Код', readonly: true},
                    {type: 'newcolumn'},
                    {type: 'button', name: 'update_code', offsetTop: 0, value: 'Обновить код'}
                ]},
                {type: 'input', name: 'name', label: 'Название', rows: 3},
                {type: "checkbox", name: 'god_type', label: "С отдельной страницей"},
                {type: 'input', name: 'image_path', label: 'Изображения'},
                {type: 'input', name: 'manufacture', label: 'Производитель'}
            ]},
            {type: 'newcolumn', offset: 100},
            {type: 'container', name: 'tree', inputHeight: 200, inputWidth: 400},
            {type: 'newcolumn'},
            {type: 'fieldset', width: 1200, label: 'Описание', name: 'description_keys', list: []}
        ]}
    ];
    form.loadStruct(formConfig, "json");
    form.updateFormData = function(data) {
        this.oldGoodCode = data['key_item'];
        var updater = {
            description: function(form, entity) {
                var descriptions = (entity.description || '').split('|');
                for (var descIndex = 0; descIndex < descriptions.length; descIndex++) {
                    var description = descriptions[descIndex].split('=');
                    form.setItemValue(description[0], description[1]);
                }
            },
            god_type: function(form, entity) {
                var value = true;
                if (!entity._isNew && !U.hasContent(entity.god_type) || entity.god_type === 'SIMPLE') {
                    value = false;
                }
                form.setItemValue('god_type', value);
            }
        }
        if (U.hasContent(data.key_item)) {
            var radioId = data.key_item.substring(0,2);
            $('#' + radioId + '').prop("checked", true);
            app.treeGoodsKeys.openItem(radioId);
            app.treeGoodsKeys.selectItem(radioId);
        }
        components.updateFormData(this, data, updater);
    }

    form.updateDescriptionConfig = function(data) {
        if (data) {
            var keysHeight = {
                k_main: 5,
                k_material: 5
            };
            var newColumn = true;
            for (var key in data) {
                if (U.hasContent(data[key])) {
                    var item = {type: 'input', name: key, label: data[key], note: {text: key}, rows: keysHeight[key] || 5, position: 'label-top', inputWidth: 350, labelWidth: 350};
                    form.addItem('description_keys', {type: 'newcolumn', offset: 20});
                    form.addItem('description_keys', item);
                }
            }
        }
    }

    form.attachEvent("onButtonClick", function(name){
        if (name == 'update_code') {
            var goodCodeKey = $('input[type=radio]:checked', app.treeGoodsKeys.allTree).attr('id');
            serviceWrapper.getNextGoodCode(goodCodeKey, function(data) {
                if (data) {
                    app.form.setItemValue('key_item', data);
                }
            });
        }
    });
    form.lock();

    return form;
}

function initTreeGoodsKeys(form) {
    var tree = new dhtmlXTreeObject(form.getContainer("tree"), '100%', '100%', 'GN');
    tree.setImagePath(app.dhtmlxImgsPath + 'dhxtree_skyblue/');
    loader.reloadGoodsKeysTree(tree);
    $(tree.allTree).attr('v-core-tree', 'goods_keys');
    return tree;
}

function initImages(tabbar) {
    tabbar.addTab('b', 'Изображения');
    tabbar.attachEvent("onSelect", function(id, lastId){
        if (id == 'b') {
            loadImages()
        }
        return true;
    })

    function loadImages() {

    }
    var dataView = tabbar.cells('b').attachDataView({
        type: {
            template:"<div class='image_template'>#image##title#</div>",
            height: 220,
            width: 130
        }
    });
    dataView.setCustomUserData = function(id, userData) {
        if (!this.customUserData) {
            this.customUserData = {};
        }
        this.customUserData[id] = userData;
    }
    dataView.getCustomUserData = function(id) {
        if (this.customUserData) {
            return this.customUserData[id];
        }
        return null;
    }
    dataView.getItemViewButtons = function(id) {
        return "<div class='move_prev' onclick=\"app.images.movePrev('" + id.trim() + "')\">&lt;</div>" +
            "<div class='move_next' onclick=\"app.images.moveNext('" + id.trim() + "')\">&gt;</div>" +
            "<div class='delete_btn' onclick=\"app.images.remove('" + id.trim() + "')\">x</div>";
    }

    dataView.hasChanges_ = function() {
        
    }

    dataView.moveNext = function(id) {
        var srcIndex = this.indexById(id);
        var nextId = this.next(id);
        if (nextId == 'add') {
            this.move(id, 0);
        } else {
            this.move(id, srcIndex + 1);
        }
    };

    dataView.movePrev = function(id) {
        var srcIndex = this.indexById(id);
        if (srcIndex == 0) {
            this.move(id, this.indexById('add') - 1);
        } else {
            this.move(id, srcIndex - 1);
        }

    };

    dataView.attachEvent('onBeforeSelect', function(id, state) {
        if (id == 'add') {
            return false;
        } else {
            
        }

    })
    dataView.attachEvent("onBeforeDrop", function (context, ev){
        if (context.target == 'add' || context.start == 'add') {
            return false;
        }
    });
    dataView.attachEvent("onBeforeDrag", function(context, ev) {
        if (context.start == 'add') {
            return false;
        }
    })
    dataView.openDialog = function() {
        if (!this.isLocked()) {
            $('#file_upload').trigger('click');
        }
    }
    dataView.saveImages = function(id, callback) {
        var instance = this;
        var key;
        var images = [];
        var index = 0;
        for(key = this.first();;key = this.next(key)) {
            if (!key || key == 'add') {
                break;
            }
            var isNew = key.indexOf('new_') == 0;
            var src = $('div[dhx_f_id=' + key + '] img').attr('src');
            if (isNew === false) {
                var matches = /^(.*)\?\d*/.exec(src);
                src = matches.length > 1? matches[1]: src;
            }
            images.push({index: index++, new: isNew, image: src});
        }

        serviceWrapper.uploadImagesForGood(id, app.form.oldGoodCode, images, callback);

    }
    dataView.deleteFile = function(id) {
        var userData = this.getCustomUserData(id);
        userData.removed = true;
        this.remove(id);
    }

    dataView.uploadFile = function(e) {
        var instance = this;
        var file = e.files[0];
        var reader = new FileReader();
        reader.onload = function(e) {
            var index = instance.indexById('add');
            var id = 'new_' + U.getRandomString();
            instance.setCustomUserData(id, {image: reader.result})
            instance.add({
                id: id,
                image: '<img src="' + reader.result + '">' + instance.getItemViewButtons(id)
            }, index);
        }
        reader.readAsDataURL(file);
    }

    dataView.lock = function(state) {
        if (state) {
            $(this._obj).addClass('disable').removeClass('enable');
        } else {
            $(this._obj).addClass('enable').removeClass('disable');
        }
    }

    dataView.isLocked = function() {
        return $(this._obj).hasClass('disable');
    }

    dataView.loadImages = function(id) {
        var instance = this;
        instance.clearAll();
        instance.addDefaultItem();
        instance.lock(false);
        serviceWrapper.getGoodImages(id, function(images) {
            if (images && images.length) {
                images.reverse();
                for (var imageIndex = 0; imageIndex < images.length; imageIndex++) {
                    var pattern = new RegExp('[^\/]*$');
                    var res = pattern.exec(images[imageIndex]);
                    var id = U.getRandomString();
                    instance.setCustomUserData(id, {image: images[imageIndex]});
                    instance.add({
                        id: id,
                        image: '<img src="/' + images[imageIndex]  + '?' + Date.now() + '" class="exist_image"><div class="title">' + (res.length ? res[0]: '') + '</div>' +  instance.getItemViewButtons(id)
                    }, 0);
                }
            }
        })
    }

    dataView.addDefaultItem = function() {
        var defaultItem = {
            id: 'add',
            image: "<div onclick='app.images.openDialog(this);' class='image_template image_template_add_btn'>+</div>\
                <form enctype='multipart/form-data' id='file_load_form'>\
                <input type='file' style='visibility:hidden;' name='goods_images' onchange='app.images.uploadFile(this)' id='file_upload' />\
                </form>"};
        this.add(defaultItem, 0);
    }

    dataView.addDefaultItem();
    dataView.lock(true);
    return dataView;
}

function initToolbar(layout, grid) {
    var handlers = {
        reload: function() {
            loader.reloadGrid(app.grid);
            app.form.lock();
            app.form.oldGoodCode = null;
            app.images.clearAll();
            app.images.addDefaultItem();
            app.images.lock(true);
        },
        add: function() {
            if (app.serviceEntities.canCreateNewEntity()) {
                app.form.unlock();
                app.form.oldGoodCode = null;
                var entity = app.serviceEntities.createNewEntity();
                var array = components.prepareEntityToGrid(entity, ['id', 'key_item', 'name']);
                grid.addRow(entity.id, array, 0);
                grid.setUserData(entity.id, 'entity', entity);
                app.form.clear();
                app.images.clearAll();
                app.images.lock(false);
                grid.selectRowById(entity.id);
            }
        },
        save: function() {
            var rowId = app.grid.getSelectedRowId();
            var entity = app.grid.getUserData(rowId, 'entity');
            var oldRowId = rowId;
            var updater = {
                description: function(form, entity) {
                    var formData = app.form.getFormData();
                    var descriptions = [];
                    for (var key in formData) {
                        if (key.indexOf('k_') == 0) {
                            descriptions.push(key + '=' + formData[key]);
                        }
                    }
                    entity.description = descriptions.join('|');
                },
                god_type: function(form, entity) {
                    var formData = app.form.getFormData();
                    entity.god_type = formData.god_type? 'HARD': 'SIMPLE';
                }
            }
            components.updateEntity(app.form, entity, updater);
            app.layout.progressOn();
            function reloadRow(entity) {
                app.grid.changeRowId(oldRowId, entity.id);
                entity._tree = prepareTree(entity._tree);
                app.grid.setUserData(entity.id, 'entity', entity);
                components.updateGridRow(app.grid, entity.id, entity, ['id', 'key_item', 'name', '_tree']);
                app.grid.clearSelection();
                app.grid.selectRowById(entity.id);
            }
            function callback(data) {
                function callback() {
                    serviceWrapper.getGood(data, reloadRow);
                    app.layout.progressOff();
                }
                app.serviceEntities.removeAll();
                app.images.saveImages(data, callback);

            }
            if (entity.hasOwnProperty('_isNew')) {
                entity.id = null;
            }
            components.prepareEntity(entity);
            serviceWrapper.updateGood(entity.id, entity, callback);
        },

        delete: function() {
            var name = app.form.getItemValue('name');
            dhtmlx.confirm({
                title:"Удаление товара",
                ok:"Да", cancel:"Отмена",
                text:"Вы уверены что хотите удалить <br>'" + name + "' ?",
                callback: function(result) {
                    if (result === true) {
                        var rowId = app.grid.getSelectedRowId();
                        var entity = app.grid.getUserData(rowId, 'entity');
                        function callback() {
                            app.grid.deleteRow(rowId);
                            app.serviceEntities.removeAll();
                            app.form.clear();
                            app.form.lock();
                        }
                        if (entity.hasOwnProperty('_isNew')) {
                            callback();
                        } else {
                            serviceWrapper.deleteGood(entity.id, function(result) {
                                if (result > 0) {
                                    callback();
                                } else {
                                    console.info('nothing to delete!');
                                }
                            });
                        }
                    }
                }});
        }
    };
    return components.createToolbar(layout, handlers);
}

function initServiceEntities() {
    var entity = {
        description: null,
        god_type: null,
        id: null,
        image_path: null,
        individual: null,
        key_item: null,
        name: null,
        person: null
    }
    return new ServiceEntities().init(entity, 'id', 1);
}

var loader = {
    reloadGoodsKeysTree: function(tree) {
      serviceWrapper.getGoodsKeys(function(data) {
          if (data.length) {
              var preparedData = [];
              var allKeys = {};
              for (var dataIndex = 0; dataIndex < data.length; dataIndex++) {
                  var entity = data[dataIndex];
                  //delete withoutChilds[entity.parent_key];
                  preparedData.push([entity.key_item, entity.parent_key, '<b>' + entity.key_item + '</b> ' + entity.value])

                  allKeys[entity.key_item] = allKeys[entity.key_item] || 0;
                  allKeys[entity.parent_key] = 1;
              }

              for (var dataIndex = 0; dataIndex < preparedData.length; dataIndex++) {
                  if (allKeys[preparedData[dataIndex][0]] === 0) {
                      preparedData[dataIndex][2] = '<input type="radio" name="sex" value="male" class="tree_radios" id="' + preparedData[dataIndex][0] + '"style="position: relative;top: 2px;">' + preparedData[dataIndex][2];
                  }
              }
              tree.loadJSArray(preparedData);
          }
      })
    },
    reloadGrid: function(grid) {
        serviceWrapper.getGoods(null, function(goods) {
            grid.clearSelection();
            grid.clearAll();
            app.form.clear();
            app.serviceEntities.removeAll();
            var gridState = {
                hasSelection: false
            }
            app.toolbar.onStateChange(gridState);
            if (goods.length) {
                for (var goodIndex = 0; goodIndex < goods.length; goodIndex++) {
                    goods[goodIndex]._tree = prepareTree(goods[goodIndex]._tree);
                    var array = components.prepareEntityToGrid(goods[goodIndex], ["id", "key_item", "name", "_tree"]);
                    grid.addRow(goods[goodIndex].id, array);
                    grid.setUserData(goods[goodIndex].id, 'entity', goods[goodIndex]);
                }
                grid.sortRows(0,"int","asc");
            }
        });
    }
}


function prepareTree(tree) {
    var ret = "";
    for (var treeIndex = 0; treeIndex < tree.length; treeIndex++) {
        if (U.hasContent(tree[treeIndex].value)) {
            ret += "<div class=\"tree_value\">" + tree[treeIndex].value + " (" + tree[treeIndex].key + ")</div>"
        }
    }
    return ret;
}
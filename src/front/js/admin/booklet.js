$(document).ready(function() {
    initHandlebarsExtensions();
    initHandlebarsTemplates();
    if (U.hasContent(URL_PARAMS.id)) {
        initFullPreviewPage(URL_PARAMS.id);
    } else {
        initPage();
    }
})

function initFullPreviewPage(id) {
    var sizeRatio = 4;
    var initialViewportSize = {
        width: 1000,
        height: 720
    }
    function updateItemSize(dom, size) {
        dom.style.width = (size.width * sizeRatio || 0) + 'px';
        dom.style.height = (size.height * sizeRatio || 0) + 'px';
    };

    function updateItemPosition(dom, position) {
        TweenLite.to(dom, 0, {
            x: position.x * sizeRatio,
            y: position.y * sizeRatio
        });
    };
    serviceWrapper.getBooklet(id, {}, function(booklet) {
        document.body.style.overflow = 'initial';
        var container = document.createElement('DIV');
        container.id = 'fullpreview';
        document.body.appendChild(container);
        var dom = U.compilePrefillHandlebar('fullpreviewContainer', booklet);
        container.appendChild(dom);
        var viewport = document.getElementsByClassName('viewport')[0];
        viewport.style.width = (initialViewportSize.width * sizeRatio) + 'px';
        viewport.style.height = (initialViewportSize.height * sizeRatio) + 'px';
        if (booklet.listItems && booklet.listItems.length) {
            booklet.listItems.forEach(function(item) {
                var itemDOM = document.getElementById(item.id);
                if (itemDOM) {
                    updateItemPosition(itemDOM, item.position);
                    updateItemSize(itemDOM, item.size);
                } else {
                    console.warn('DOM object for id=' + item.id + ' was not rendered!')
                }
            })
        }
    })
}

function initPage() {
    U.dhtmlxDOMPreInit(document.documentElement, document.body);
    unSelectionStateDetails = {
        items: {
            add: {
                enableState: false
            },
            loadBackground: {
                enableState: false
            },
            preview: {
                enableState: false
            }
        }
    };
    selectionState = {
        items: {
            save: {
                enableState: true
            },
            delete: {
                enableState: true
            }
        }
    };
    selectionStateDetails = {
        items: {
            add: {
                enableState: true
            },
            loadBackground: {
                enableState: true
            }
        }
    };

    unSelectionState = {
        hasSelection: false
    };
    app.layout = initLayout();
    app.menu = initMenu();
    app.booklet = initBookletPreview(app.layout);
    app.bookletsList = initBookletsList(app.layout);
    app.bookletsToolbar = initBookletsToolbar(app.layout, app.bookletsList);
    app.bookletDetailsToolbar = initBookletToolbar(app.layout);
    app.bookletsList.reload();
}

function initLayout() {
    var layout = new dhtmlXLayoutObject({
        parent: document.body,
        pattern: "2U",
        cells: [
            {id: "a", text: "Буклеты", width: 400},
            {id: "b", text: "Предпросмотр"}
        ]
    });
    layout.detachHeader();
    return layout;
}

function initMenu() {
    return components.createMenu(app.layout);
}

function initBookletsList(layout, booklet) {
    var grid = layout.cells("a").attachGrid();
    grid.setImagePath(app.dhtmlxImgsPath);
    grid.setHeader("ID, Название");
    grid.setInitWidths("60,320");
    grid.setColAlign("left,left");
    grid.setColTypes("ro,ro");
    grid.setColSorting("int,str");
    grid.init();

    grid.attachEvent("onSelectStateChanged", function(id){
        var entity = this.getUserData(id, 'entity');
        var gridState = {
            hasSelection: true
        }
        app.bookletsToolbar.onStateChange(gridState);
        app.bookletDetailsToolbar.onStateChange(gridState);
        if (!app.booklet.isEmpty() && app.booklet.isNew()) {
            return;
        }
        app.booklet.controller.load(id);
        /*serviceWrapper.getBooklet(id, {}, function(data) {
            app.booklet.clear();
            if (U.hasContent(data)) {
                app.booklet.populate(data);
            }
        })*/
    });


    grid.reload = function() {
        serviceWrapper.listBooklets({id: 'booklet_id', name: 'name'}, function(booklets) {
            components.reloadGrid(grid, booklets, ['id', 'name']);
            app.bookletsToolbar.onStateChange({hasSelection: false});
            app.bookletDetailsToolbar.onStateChange(unSelectionStateDetails);
        })
    }

    grid.addNewBooklet = function() {
        var newBooklet = app.booklet.createNewBooklet();
        var rowArray = components.prepareEntityToGrid(newBooklet, ['id', 'name']);
        this.addRow(newBooklet.id, rowArray, 0);
        this.selectRowById(newBooklet.id);
    }

    return grid;
}

function initBookletsToolbar(layout, grid) {
    var handlers = {
        reload: function() {
            app.bookletsList.reload();
            app.booklet.clear();
        },
        add: function() {
            if (app.booklet.canCreateNew()) {
                app.bookletsList.addNewBooklet();
                app.bookletsToolbar.onStateChange(selectionState);
                app.bookletDetailsToolbar.onStateChange(selectionStateDetails);
            }
        },
        save: function() {
            app.booklet.save(function(oldId, newId) {
                app.bookletsToolbar.onStateChange(selectionState);
                app.bookletDetailsToolbar.onStateChange(selectionStateDetails);
                serviceWrapper.getBooklet(newId, {id: 'booklet_id', name: 'name'}, function(data) {
                    components.updateGridRow(app.bookletsList, oldId, data, ['id', 'name'], newId);
                    app.bookletsList.clearSelection();
                    app.bookletsList.selectRowById(newId);
                })
            });
        },
        delete: function() {
            app.booklet.delete(function() {
                app.bookletsList.deleteSelectedRows();
                app.bookletsToolbar.onStateChange(unSelectionState);
                app.bookletDetailsToolbar.onStateChange(unSelectionStateDetails);
            });
        }
    };
    var toolbar = components.createToolbar(layout, handlers);
    toolbar.onStateChange({hasSelection: false});
    return toolbar;
}

function initBookletToolbar(layout) {
    var handlers = {
        add: function() {
            app.booklet.addNewItem();
        },
        loadBackground: function() {
            bookletBackgroundWindow.show();
        },
        preview: function() {
            var entity = app.booklet.controller.getEntity();
            if (!entity._isNew) {
                window.open(window.location + '?id=' + entity.id);
            }
        }
    }

    bookletBackgroundWindow.addSelectCallback(function(imageObject) {
        if (imageObject) {
            app.booklet.updateBackground(imageObject.image);
        }
    });
    var toolbar = components.createToolbar(layout, handlers, ['add', 'loadBackground', 'preview'], 'b');
    toolbar.onStateChange(unSelectionStateDetails);
    return toolbar;
}

function initBookletPreview(layout, grid) {
    layout.cells('b').attachHTMLString("<div id='booklets'></div>");
    var booklet = new BookletComponent(document.getElementById('booklets'), new BookletController().init()).init();
    //booklet.enable();
    return booklet;
}

/***************************************************************/
BookletComponent = function(domParentContainer, controller) {
    this.$domParentContainer = $(domParentContainer);
    this.$bookletDOM = null;
    this.$bookletItemsDOM = null;
    this.listeners = {};
    this.bookletItems = [];
    this.controller = controller;
    this.controller.owner = this;
    this.items = [];
    this.editorWindow = undefined;
    this.gridRyleCode = '3c2c';
    this.itemSizeCode = '135x225';
    this.initGridRules();
    this.initSizeRules();
};

BookletComponent.prototype.init = function() {
    var editorWindowController = new BookletEditorController();
    this.editorWindow = new BookletEditorComponent(this).init(editorWindowController);

    //this.enable(false);
    return this;
};

BookletComponent.prototype.render = function() {
    var entity = this.controller.getEntity();
    if (!this.$bookletDOM) {
        this.$bookletDOM = U.compilePrefillHandlebar("previewContainer", entity);
        this.$domParentContainer.append(this.$bookletDOM);
        this.$bookletDOM = $(this.$bookletDOM);
        this.gridRules[this.gridRyleCode].preRender();
        this.$bookletItemsDOM = $('.viewport', this.$bookletDOM);
    } else {
        this.renderBorders(entity.bordersTemplate);
        this.renderPalls();
    }
    $('.viewport', this.$bookletDOM).html('');
    if (entity.listItems && entity.listItems.length) {
        var bookletInstance = this;
        entity.listItems.forEach(function(bookletItemEntity) {
            var bookletItem = new BookletItem(bookletInstance, bookletInstance.$bookletItemsDOM, bookletInstance.editorWindow).init(bookletItemEntity);
            bookletInstance.bookletItems.push(bookletItem);
            bookletItem.render();
        })
    }
};

BookletComponent.prototype.updateBackground = function(image) {
    this.controller.updateBackground(image);
    $('.background img',this.$bookletDOM).attr('src', image);
};

BookletComponent.prototype.renderBorders = function(bordersTemplate) {
    $('.borders', this.$bookletDOM).html(Handlebars.compile($('#' + bordersTemplate).html())());
};

BookletComponent.prototype.renderPalls = function() {

};

BookletComponent.prototype.prepareItem = function(item, data) {
    var result = item;
    if (typeof data != 'undefined' && Object.keys(data).length) {
        for (var key in data) {
            result = result.replace(new RegExp('({{' + key + '}})+'), U.hasContent(data[key])? data[key]: '');
        }
    }
    return result;
};

BookletComponent.prototype.clearTemplate = function(template) {
    if (typeof template == 'string') {
        return template.replace(/({{[^}]*}})+/g, '');
    }
    return '';
};

BookletComponent.prototype.enable = function(state) {
    state = typeof state == 'undefined'? true: state;
    this.$bookletDOM.removeClass(state ? 'disable': 'enable').addClass(state? 'enable': 'disable');
    return this;
};

BookletComponent.prototype.showEditor = function(itemId) {
    this.editorWindow.show();
    this.editorWindow.populateData(this.controller.horizontalEntity[itemId]);
};

BookletComponent.prototype.clearItem = function(id) {
    var entity = this.controller.clearItem(id);
    this.render(entity);
};

BookletComponent.prototype.clear = function() {
    this.$domParentContainer.html("");
    this.$bookletDOM = null;
    this.$bookletItemsDOM = null;
    this.controller.clear();
    if (this.bookletItems && this.bookletItems.length) {
        this.bookletItems.forEach(function(value, index) {
            value.unload();
        })
    }
    this.bookletItems = [];
    return this;
};

BookletComponent.prototype.save = function(callback) {
    this.controller.save(callback);
};

BookletComponent.prototype.delete = function(callback) {
    this.controller.delete(callback);
};

BookletComponent.prototype.populate = function(entity) {
    app.layout.progressOn();
    this.clear();
    this.controller.setEntity(entity);
    this.propertyChange('state', [entity]);
    this.render(entity);
    app.layout.progressOff();
    return entity;
};

BookletComponent.prototype.createNewBooklet = function() {
    this.clear();
    var entity = this.controller.createNewBooklet();
/*    this.propertyChange('state', [entity]);*/
    this.render(entity);
    return entity;
};

BookletComponent.prototype.canCreateNew = function() {
    return this.controller.canCreateNew();
};

/*BookletComponent.prototype._getHtml = function(entity, items, template) {
    var result = '';
    template = this.prepareItem(template, entity);
    var templates = template.split('{{content}}');
    if (templates.length > 1) {
        result += templates[0];
        if (items && items.length) {
            for (var itemIndex = 0; itemIndex < items.length; itemIndex++) {
                result += this.getEntityHtmlObj(items[itemIndex]).htmlString;
            }
        }
        result += templates[1];
    } else {
        result = template;
    }
    return result;
};*/

/*BookletComponent.prototype.getEntityHtmlObj = function(entity) {
    var result = '';
    if (U.hasContent(entity)) {
        var type = this.getType(entity);
        switch(type) {
            case this.controller.itemTypes.booklet:
                result = this._getHtml(entity, entity.listColumns, this.templates[type]);
                break;
            case this.controller.itemTypes.item:
                var clone = entity;
                if (entity.image && entity.image.length > 0 && entity.image.indexOf('data:image') != 0) {
                    clone = $.extend({}, entity);
                    clone.image = formatBookletImage(clone.image);
                }
                result = this._getHtml(clone, clone.listLabels, this.templates[type]);
                break;
            case this.controller.itemTypes.label:
                result = this._getHtml(entity, [], this.templates[type]);
                break;
        }
    }
    //TODO next line throw exception when entity undefined - its just because double handling clear, edit and delete events(
    return {
        id: entity.id,
        htmlString: result
    }
};*/

BookletComponent.prototype.initGridRules = function() {
    this.gridRules = {
        '3c2c': {
            x: function(x, width) {
                return this._additional.xyHandle(
                    x, width,
                    this._additional.xLeft,
                    this._additional.xRight,
                    this._additional.xMagnetize
                );
            },
            y: function(y, height) {
                return this._additional.xyHandle(
                    y, height,
                    this._additional.yTop,
                    this._additional.yBottom,
                    this._additional.yMagnetize
                );
            },
            highLightX: function(leftX, rightX) {
                var $verticalTop = $('.vertical-border-1');
                var $verticalBottom = $('.vertical-border-2');

                $verticalTop.finish();
                $verticalTop.css('left', (leftX? leftX: 0) + 'px');
                $verticalTop[this._additional.xLeft.indexOf(leftX) > -1? 'show': 'hide'](100);

                $verticalBottom.finish();
                $verticalBottom.css('left', (rightX? rightX: 0) + 'px');
                $verticalBottom[this._additional.xRight.indexOf(rightX) > -1? 'show': 'hide'](100);
            },
            highLightY: function(topY, bottomY) {
                var $horizontalLeft = $('.horizontal-border-1');
                var $horizontalRight = $('.horizontal-border-2');
                $horizontalLeft.finish();
                $horizontalLeft.css('top', (topY? topY: 0) + 'px');
                $horizontalLeft[this._additional.yTop.indexOf(topY) > -1? 'show': 'hide'](100);

                $horizontalRight.finish();
                $horizontalRight.css('top', (bottomY? bottomY: 0) + 'px');
                $horizontalRight[this._additional.yBottom.indexOf(bottomY) > -1? 'show': 'hide'](100);
            },
            preRender: function() {
                var $container = $('#preview_container .borders');
                var vars = this._additional;
                var horBordersPositions = vars.yTop.concat(vars.yBottom);
                var verBordersPositions = vars.xLeft.concat(vars.xRight);
                horBordersPositions.forEach(function(position) {
                    var border = document.createElement('DIV');
                    border.setAttribute('class', 'vertical-border shadow-item-border item-border vertical');
                    border.style.top = position + 'px';
                    $container.prepend(border);
                });
                verBordersPositions.forEach(function(position) {
                    var border = document.createElement('DIV');
                    border.setAttribute('class', 'horizontal-border shadow-item-border item-border horizontal');
                    border.style.left = position + 'px';
                    $container.prepend(border);
                });
            },
            clearHightLight: function() {
                $('.vertical-border-1, .vertical-border-2, .horizontal-border-1 ,.horizontal-border-2').hide();
            },
            _additional: {
                xyHandle: function(pos, size, poss1, poss2, magnetize) {
                    var pos1 = pos;
                    var pos2 = pos + size;
                    pos1 = this.checkCoordinates(pos1, poss1, magnetize);
                    pos2 = this.checkCoordinates(pos2, poss2, magnetize);
                    return [pos1, pos2];
                },
                checkCoordinates: function(pos, poss, magnetize) {
                    for (var posIndex = 0; posIndex < poss.length; posIndex++) {
                        var poz = poss[posIndex];
                        if (pos >= poz - magnetize && pos <= poz + magnetize) {
                            return poz;
                        }
                    }
                    return null;
                },
                xMagnetize: 30,
                yMagnetize: 30,
                xRight: [165, 315, 480, 630, 795, 945],
                xLeft: [30, 180, 345, 495, 660, 810],
                yTop: [15, 245, 475],
                yBottom: [240, 470, 700]
            }
        }
    }
};

BookletComponent.prototype.initSizeRules = function() {
    this.sizeRules = {
        '135x225': {
            width: function(width) {
                return Math.min(
                    Math.round(width / this._additional.basicWidth) * this._additional.basicWidth || this._additional.basicWidth,
                    this._additional.maxWidth);
            },
            height: function(height) {
                return Math.min(
                    Math.round(height / this._additional.basicHeight) * this._additional.basicHeight || this._additional.basicHeight,
                    this._additional.maxHeight);
            },
            _additional: {
                basicWidth: 135,
                basicHeight: 225,
                maxWidth: 135*2,
                maxHeight: 225*3
            }
        }
    }
};

BookletComponent.prototype.checkPosToSnapX = function(x, width) {
    var xSnap = this.gridRules[this.gridRyleCode].x(x, width);
    return xSnap;
};

BookletComponent.prototype.checkPosToSnapY = function(y, height) {
    var ySnap = this.gridRules[this.gridRyleCode].y(y, height);
    return ySnap;
};

BookletComponent.prototype.highLightX = function(xLeft, xRight) {
    this.gridRules[this.gridRyleCode].highLightX(xLeft, xRight);
};

BookletComponent.prototype.highLightY = function(yTop, yBottom) {
    this.gridRules[this.gridRyleCode].highLightY(yTop, yBottom);
};

BookletComponent.prototype.clearHightLight = function() {
    this.gridRules[this.gridRyleCode].clearHightLight();
};

/*BookletComponent.prototype.renderBooklet = function(data) {
    this.bookletItems = [];
    if (data && data.length) {
        for(var itemIndex = 0; itemIndex < data.length; itemIndex++) {
            var item = new BookletItem(this.$bookletItemsDOM, this.editorWindow).init(data[itemIndex]).render();
            item.booklet = this;
            this.bookletItems.push(item);
        }
    }
};*/

BookletComponent.prototype.addNewItem = function() {
    var item = new BookletItem(this, this.$bookletItemsDOM, this.editorWindow).createNew().render();
    this.bookletItems.push(item);
};

BookletComponent.prototype.deleteItem = function() {
    if (arguments.length && U.hasContent(arguments[0])) {
        var bookletInstance = this;
        var parameter = arguments[0];
        // it's mean id
        if (typeof parameter == 'number') {
            this.bookletItems.forEach(function(item, index) {
                if (item.id == parameter) {
                    this.splice(index, 1);
                    return false;
                }
            })
        }
        // it's mean item instance
        else if (U.isObject(arguments)) {
            this.bookletItems.forEach(function(item, index) {
                if (item == parameter) {
                    bookletInstance.bookletItems.splice(index, 1);
                    return false;
                }
            })
        }
    }
};

BookletComponent.prototype.isNew = function() {
    return !!this.controller.entity._isNew;
};

BookletComponent.prototype.isEmpty = function() {
    return !U.hasContent(this.controller.entity);
};

BookletComponent.prototype.getItemSize = function(id) {
    return this.controller.horizontalEntity[id].size;
};

BookletComponent.prototype.updateConnectors = function(id) {
    var instance = this;
    var maxRowCount = 3;
    var maxColumnCount = 2;
    var matrix = (function (rowsCount, columnsCount) {
        var matrix = {};
        for (var rowIndex = 0; rowIndex < rowsCount; rowIndex++) {
            matrix[rowIndex] = {};
            for (var columnIndex = 0; columnIndex < columnsCount; columnIndex++) {
                matrix[rowIndex][columnIndex] = {top: [], right: [], bottom: [], left: [], isEmpty: true, data: {}};
            }
        }
        return matrix;
    })(maxRowCount, maxColumnCount);

    function findPalls(rowInd, colInd, count, way) {
        var result = [];
        if (U.hasContent(matrix[rowInd]) && U.hasContent(matrix[rowInd][colInd])) {
            if (way == 'vertical') {
                for (var stepIndex = 0; stepIndex < count && U.hasContent(matrix[rowInd + stepIndex][colInd]); stepIndex++) {
                    result.push(matrix[rowInd + stepIndex][colInd]);
                }
            } else if (way == 'horizontal') {
                for (var stepIndex = 0; stepIndex < count && U.hasContent(matrix[rowInd][colInd + stepIndex]); stepIndex++) {
                    result.push(matrix[rowInd][colInd + stepIndex]);
                }
            }
        }
        return result;
    }

    function getPositions(rowInd, colInd, rowsCount, collsCount) {
        var result = [];
        for (var horIndex = 0; horIndex < collsCount; horIndex++) {
            for (var verIndex = 0; verIndex < rowsCount; verIndex++) {
                result.push([rowInd + verIndex, colInd + horIndex]);
            }
        }
        return result;
    }

    function isPositionsEmpty(positions) {
        var result = true;
        if (positions.length && !U.isArray(positions[0])) {
            positions = [positions];
        }
        for (var posIndex = 0; posIndex < positions.length; posIndex++) {
            var rowInd = positions[posIndex][0];
            var colInd = positions[posIndex][1];
            if (!U.hasContent(matrix[rowInd]) || !U.hasContent(matrix[rowInd][colInd]) || U.hasContent(matrix[rowInd][colInd].isEmpty === false)) {
                result = false;
                break;
            }
        }
        return result;
    }

    function buildMatrix(columnItems) {
        function addToEndInMatrix(size, data) {
            function fillMatrix(positions, matrixItem) {
                for (var posIndex = 0; posIndex < positions.length; posIndex++) {
                    var rowInd = positions[posIndex][0];
                    var colInd = positions[posIndex][1];
                    $.extend(true, matrixItem[rowInd][colInd], data);
                }
            }
            var sizeHandlers = {
                1: function(rowInd, colInd, data) {
                    matrix[rowInd][colInd].top = findPalls(rowInd - 1, colInd, 1, 'horizontal');
                    matrix[rowInd][colInd].right = findPalls(rowInd, colInd + 1, 1, 'vertical');
                    matrix[rowInd][colInd].bottom = findPalls(rowInd + 1, colInd, 1, 'horizontal');
                    matrix[rowInd][colInd].left = findPalls(rowInd, colInd - 1, 1, 'vertical');
                    matrix[rowInd][colInd].isEmpty = false;
                    matrix[rowInd][colInd].data = data;
                    return true;
                },
                2: function(rowInd, colInd, data) {
                    var positions = getPositions(rowInd, colInd, 2);
                    if (isPositionsEmpty(positions)) {
                        matrix[rowInd][colInd].top = findPalls(rowInd - 1, colInd, 2, 'horizontal');
                        matrix[rowInd][colInd].right = findPalls(rowInd, colInd + 2, 2, 'vertical');
                        matrix[rowInd][colInd].bottom = findPalls(rowInd + 2, colInd, 2, 'horizontal');
                        matrix[rowInd][colInd].left = findPalls(rowInd, colInd - 1, 2, 'vertical');
                        matrix[rowInd][colInd].isEmpty = false;
                        matrix[rowInd][colInd].data = data;
                        fillMatrix(positions, matrix[rowInd][colInd])
                    }
                }
            }
            var breakk = false;
            for (var rowIndex = 0; rowIndex < maxRowCount && breakk === false; rowIndex++) {
                for (var columnIndex = 0; columnIndex < maxColumnCount && breakk === false; columnIndex++) {
                    var item = matrix[rowIndex][columnIndex];
                    if (item.isEmpty === true && sizeHandlers.hasOwnProperty(size)) {
                        breakk = sizeHandlers[size](rowIndex, columnIndex, data);
                    }
                }
            }
        }
        for (var itemIndex = 0; itemIndex < columnItems.length; itemIndex++) {
            addToEndInMatrix(
                instance.getItemSize(columnItems[itemIndex].id),
                columnItems[itemIndex]
            );
        }
    }

    function getNeighbors($parent, id) {
        var itemsInRowCount = 2;
        var $neighbors = $('.item', $parent);
        var wholeItemsCount = $neighbors.length;
        var idIndex = -1;
        var currentSize = instance.getItemSize(id);
        for (var neiIndex = 0; neiIndex < $neighbors.length; neiIndex++) {
            if ($neighbors[neiIndex].id == id) {
                idIndex = neiIndex;
            }
        }
        //find id position
        if (idIndex == -1) {
            return null;
        }
        /*if ()*/
        return {top: 0, right: 0, bottom: 0, left: 0};
    }

    buildMatrix(this.controller.entity.listColumns[0].listItems);
};

BookletComponent.prototype.updateBorder = function() {

};

/***************************************************************/

BookletController = function() {}

BookletController.prototype.init = function() {
    this.horizontalEntity = {};
    var bookletEntity = {
        id: null,
        name: null,
        listItems: [],
        bordersTemplate: '3c2c',
        itemType: 'booklet',
        backgroundImage: '',
        created: null,
        updated: null
    }
    this.bookletManager = new ServiceEntities().init(bookletEntity, 'id', 1, {created: Date.now, updated: Date.now});
    this.bookletManager.generateId = generateId;

    return this;
};

BookletController.prototype.setEntity = function(entity) {
    this.entity = entity;
};

BookletController.prototype.createNewBooklet = function() {
    this.entity = this.bookletManager.createNewEntity();
    this.entity._isNew = true;
    return this.entity;
};

BookletController.prototype.updateHorizontalEntity = function(booklet) {
/*
    this.horizontalEntity = {};
    if (U.hasContent(booklet)) {
        for (var columnIndex = 0; columnIndex < booklet.listColumns.length; columnIndex++) {
            var column = booklet.listColumns[columnIndex];
            //items per column
            for (var itemIndex = 0; itemIndex < column.listItems.length; itemIndex++) {
                var item = column.listItems[itemIndex];
                this.horizontalEntity[item.id] = item;
            }
        }
    }
*/
};

BookletController.prototype.getEntity = function() {
    return this.entity;
};

BookletController.prototype.updateBackground = function(backgroundImage) {
    this.entity.backgroundImage = backgroundImage;
};

BookletController.prototype.updateEntity = function() {
    this.entity.listItems = [];
    var instance = this;
    if (this.owner.bookletItems && this.owner.bookletItems.length) {
        this.owner.bookletItems.forEach(function(bookletItem) {
            instance.entity.listItems.push(bookletItem.controller.getEntity());
        })
    }
    return this.entity;
};

BookletController.prototype.canCreateNew = function() {
    return true;
};

BookletController.prototype.load = function(id, callback) {
    var instance = this;
    serviceWrapper.getBooklet(id, {}, function(data) {
        instance.owner.clear();
        instance.owner.controller.setEntity(data);
        instance.owner.render();
        if (typeof callback == 'function') {
            callback(data);
        }
    });
};

BookletController.prototype.save = function(callback) {
    var entity = this.updateEntity();
    var id = entity.id;
    entity.id = entity._isNew? null: entity.id;
    components.prepareEntity(entity);
    serviceWrapper.saveBooklet(entity, function(result) {
        callback(id, result);
    });
};

BookletController.prototype.delete = function(callback) {
    var instance = this;
    function localCallback() {
        instance.owner.clear();
        instance.clear();
        callback();
    }
    if (!this.entity._isNew) {
        serviceWrapper.deleteBooklet(this.entity.id, localCallback);
    } else {
        localCallback();
    }
};

BookletController.prototype.clear = function() {
    this.bookletManager.removeAll();
    this.entity = null;
};

BookletController.prototype.clearItem = function(id) {
    this._clearItem(this.horizontalEntity[id]);
    return this.horizontalEntity[id];
};

BookletController.prototype._clearItem = function(entity) {
    if (U.hasContent(entity)) {
        for (var key in entity) {
            if (['id', 'itemType', '_isNew', 'type'].indexOf(key) > -1) {
            } else if (U.isArray(entity[key])) {
                for (var arrElemIndex = 0; arrElemIndex < entity[key].length; arrElemIndex++) {
                    entity[key][arrElemIndex] = this._clearItem(entity[key][arrElemIndex]);
                }
            } else if (U.isObject(entity[key])) {
                entity[key] = this._clearItem(entity[key]);
            } else {
                entity[key] = null;
            }
        }
    }
    return entity;
};
/*********************************************************************************************/

BookletItem = function(booklet, $parentContainer, editor) {
    this.booklet = booklet;
    this.$parentContainer = $parentContainer;
    this.controller = new BookletItemController();
    this.controller.owner = this;
    this.editor = editor;
};

//constructor
BookletItem.prototype.init = function(entity) {
    this.controller.setEntity(entity);
    return this;
};

//constructor
BookletItem.prototype.createNew = function() {
    var entity = this.controller.createNew();
    return this;
};

BookletItem.prototype.render = function() {
    if (U.hasContent(this.$itemDOM)) {
        var htmlDOM = Handlebars.compile($('#bookletItemContent').html())(this.controller.getEntity());
        $('.content', this.$itemDOM).html(htmlDOM);
    } else {
        this.$itemDOM = U.compilePrefillHandlebar('bookletItem', this.controller.getEntity());
        this.$parentContainer.append(this.$itemDOM);
        this.$itemDOM = $(this.$itemDOM);
        this.initEvents();
        this.updateSize();
        this.updatePosition()
    }
    return this;
};

BookletItem.prototype.getId = function() {
    var entity = this.controller.getEntity();
    return entity? entity.id: '';
};

BookletItem.prototype.initEvents = function() {
    var itemInstance = this;
    //EDIT
    this.$itemDOM.on('click', '.edit_item', function() {
        itemInstance.editor.updateRelations(itemInstance);
        itemInstance.editor.show();
        itemInstance.editor.populateData(itemInstance.controller.getEntity());
    })
    //CLEAR
    this.$itemDOM.on('click', '.clear_item', function() {
        itemInstance.clear();
    })
    //DELETE
    this.$itemDOM.on('click', '.delete_item', function() {
        itemInstance.booklet.deleteItem(itemInstance);
        itemInstance.unload();
    })

    this.draggable = undefined;
    if (U.hasContent(this.getId())) {
        var selector = "#" + this.getId();
        this.resizable = $(selector).resizable({
            delay: 0,
            resize: function(event, ui) {
                ui.size.width = itemInstance.booklet.sizeRules[itemInstance.booklet.itemSizeCode].width(ui.size.width);
                ui.size.height = itemInstance.booklet.sizeRules[itemInstance.booklet.itemSizeCode].height(ui.size.height);
                itemInstance.controller.updateEntity({size: {width: ui.size.width, height: ui.size.height}});
            }
        }).on('mouseup', function() {
            itemInstance.booklet.clearHightLight();
        })

        $('.ui-resizable-handle', this.resizable).attr('data-clickable', true);
        this.draggable = Draggable.create(selector, {
            type:"x,y",
            edgeResistance:0.5,
            autoScroll: 0.5,
            bounds: this.$parentContainer,
            lockedAxis:true,
            throwProps:true,
            snap: {
                x: function(endValue) {
                    return endValue;
                },
                y: function(endValue) {
                    return endValue;
                }
            },
            liveSnap: {
                x: function(end) {
                    var poss = itemInstance.booklet.checkPosToSnapX(end, itemInstance.controller.getSize().width);
                    itemInstance.booklet.highLightX(poss[0], poss[1]);
                    var res = U.hasContent(poss[0])? poss[0]: end;
                    itemInstance.controller.updateEntity({position: {x: res}});
                    return res;
                },
                y: function(end) {
                    var poss = itemInstance.booklet.checkPosToSnapY(end, itemInstance.controller.getSize().height);
                    itemInstance.booklet.highLightY(poss[0], poss[1]);
                    var res = U.hasContent(poss[0])? poss[0]: end;
                    itemInstance.controller.updateEntity({position: {y: res}});
                    return res;
                }
            },
            onDragEnd: function() {
                itemInstance.booklet.clearHightLight();
            }
        })
    }
};

BookletItem.prototype.clear = function() {
    this.controller.clear();
    this.render();
};

BookletItem.prototype.unload = function() {
    this.$parentContainer = null;
    this.controller = null;
    this.editor = null;
    this.$itemDOM.remove();
    this.draggable = null;
    this.resizable = null;
    this.booklet.clearHightLight();
    this.booklet = null;
};

BookletItem.prototype.updateSize = function() {
    if (this.$itemDOM) {
        var size = this.controller.getSize();
        this.$itemDOM.css('width', size.width);
        this.$itemDOM.css('height', size.height);
    }
};

BookletItem.prototype.updatePosition = function() {
    if (this.$itemDOM) {
        var position = this.controller.getPosition();
        TweenLite.to(this.$itemDOM, 0, {
            x: position.x,
            y: position.y
        });
    }
};

BookletItemController = function() {
    this.entity = null;
    this.itemTypes = {
        item: 'item',
        label: 'label'
    }
    var bookletItemEntity = {
        id: null,
        image: null,
        listLabels: [],
        number: null,
        size: {
            width: 135,
            height: 225
        },
        position: {
            x: 0,
            y: 0
        },
        itemType: this.itemTypes.item
    }
    var bookletItemLabelEntity = {
        id: null,
        type: null,
        text: null,
        itemType: this.itemTypes.label
    }

    this.clearItemTemplate = {
        image: null,
        number: null
    }
    this.clearLabelTemplate = {
        text: null
    }
    this.bookletItemManager = new ServiceEntities().init(bookletItemEntity, 'id', 999);
    this.bookletItemLabelManager = new ServiceEntities().init(bookletItemLabelEntity, 'id', 999);
    this.bookletItemManager.generateId = generateId;
    this.bookletItemLabelManager.generateId = generateId;
    this.labelTypes = {
        top_right: 'top_right',
        bottom_right: 'bottom_right',
        bottom_center: 'bottom_center'
    };
    this.labelTypes = {
        top_right_0: 'top_right_0',
        top_right_1: 'top_right_1',
        bottom_right_0: 'bottom_right_0',
        bottom_right_1: 'bottom_right_1',
        bottom_center_0: 'bottom_center_0',
        bottom_center_1: 'bottom_center_1'
    };
};

BookletItemController.prototype.createNew = function() {
    this.entity = this.bookletItemManager.createNewEntity();
    //labels per item
    var labelsTypes = Object.keys(this.labelTypes);
    for (var labelIndex = 0; labelIndex < labelsTypes.length; labelIndex++) {
        var label = this.bookletItemLabelManager.createNewEntity();
        label.type = labelsTypes[labelIndex];
        this.entity.listLabels.push(label);
    }
    return this.entity;
};

BookletItemController.prototype.setEntity = function(entity) {
    this.entity = entity;
    this.entity.position.x = isNaN(this.entity.position.x)? 0: parseFloat(this.entity.position.x);
    this.entity.position.y = isNaN(this.entity.position.y)? 0: parseFloat(this.entity.position.y);
    this.entity.size.width = isNaN(this.entity.size.width)? 135: parseFloat(this.entity.size.width);
    this.entity.size.height = isNaN(this.entity.size.height)? 225: parseFloat(this.entity.size.height);
};

BookletItemController.prototype.updateEntity = function(entity) {
    $.extend(true, this.entity || {}, entity || {});
};

BookletItemController.prototype.getEntity = function() {
    return this.entity;
};

BookletItemController.prototype.getPosition = function() {
    return this.entity.position;
};

BookletItemController.prototype.getSize = function() {
    return this.entity.size;
};

BookletItemController.prototype.clear = function() {
    var itemInstance = this;
    $.extend(this.entity, this.clearItemTemplate);
    this.entity.listLabels.forEach(function(label) {
        $.extend(label, itemInstance.clearLabelTemplate);
    })
};

/*********************************************************************************************/
BookletEditorComponent = function(parent) {
    this.parent = null;
    this.imageLoaderId = 'file_upload';
    this.imageLoader = '<div id="' + this.imageLoaderId + '"><input class="' + this.imageLoaderId + '" type="file"></div>';
    this.imagePreviewTemplate = "<div id='booklet_item_image_preview'>\
        <image id='booklet_item_image' class='booklet_item_image'/>\
        <div class='text_fields_container'>\
            <div class='text_fields_sub_container top_right'>\
                <textarea id='top_right_0' class='top_right_0 booklet_item_image_label' rows='1'></textarea>\
                <div class='text_field_line'></div>\
                <textarea id='top_right_1' class='top_right_1 booklet_item_image_label' rows='1'></textarea>\
            </div>\
            <div class='text_fields_sub_container bottom_right'>\
                <textarea id='bottom_right_0' class='bottom_right_0 booklet_item_image_label' rows='1'></textarea>\
                <div class='text_field_line'></div>\
                <textarea id='bottom_right_1' class='bottom_right_1 booklet_item_image_label' rows='1'></textarea>\
            </div>\
            <div class='text_fields_sub_container bottom_center'>\
                <textarea id='bottom_center_0' class='bottom_center_0 booklet_item_image_label' rows='1'></textarea>\
                <div class='text_field_line'></div>\
                <textarea id='bottom_center_1' class='bottom_center_1 booklet_item_image_label' rows='1'></textarea>\
            </div>\
        </div>\
    </div>";
};

BookletEditorComponent.prototype.init = function(controller) {
    this.controller = controller;
    this.labelTemplates = undefined;
    this.window = undefined;
    return this;
};

BookletEditorComponent.prototype.updateRelations = function(parent) {
    this.clear();
    this.parent = parent;
};

BookletEditorComponent.prototype.initEvents = function() {
    var instance = this;
    this.window.attachEvent('onClose', function() {
        instance.clear();
        this.hide();
    })
};

BookletEditorComponent.prototype.show = function() {
    if (typeof this.window == 'undefined') {
        var myWins = new dhtmlXWindows('dhx_blue');
        this.window = myWins.createWindow('item_editor', 500, 450, 660, 565);
        this.initEvents();
        this.form = this._initForm(this.window);
        this.labelTemplates = this._initTemplates(this.form);
    }
    var position = U.getPageCenter();
    var size = this.window.getDimension();
    var x = position.x - size[0] / 2;
    var y = position.y - size[1] / 2;
    this.window.setPosition(
        x >= 0? x: 0,
        y >= 0? y: 0
    );
    this.window.show();
};

BookletEditorComponent.prototype._resetImageLoader = function() {
    var instance = this;
    $('#' + this.imageLoaderId).replaceWith(this.imageLoader);
    $('#' + this.imageLoaderId).on('change', '.' + this.imageLoaderId, function() {
        var file = this.files[0];
        var reader = new FileReader();
        reader.onload = function() {
            instance.form.setItemValue('_image', reader.result);
            $('.booklet_item_image').attr('src', reader.result).data('isbase64', true);
            instance._resetImageLoader();
        }
        reader.readAsDataURL(file);
    })
};

BookletEditorComponent.prototype._initForm = function() {
    var instance = this;
    function initEvents(form) {
        form.attachEvent('onButtonClick', function(name) {
            switch (name) {
                case 'image_load':
                    $('#' + instance.imageLoaderId + ' .' + instance.imageLoaderId).trigger('click');
                    break;
                case 'ok':
                    var entity = instance.getData();
                    instance.close();
                    instance.parent.controller.updateEntity(entity);
                    instance.parent.render();
                    break;
            }
        })
        form.attachEvent('onButtonClick', function(name) {
            if (name == 'image_inputs_templates') {
                if (!instance.popup.isVisible()) {
                    instance.popup.show('image_inputs_templates');
                    instance.labelTemplates.refresh();
                } else {
                    instance.popup.hide();
                }
            }
        })

        $('.text_fields_container textarea').focus(function() {
            $('.text_fields_sub_container').css('z-index', 100);
            $(this).parents('.text_fields_sub_container').css('z-index', 101);
        })
        instance._resetImageLoader();
    }
    var config = [
        {type: 'settings', labelWidth: 50, labelAlign: 'right', inputWidth: 250},
        {type: 'template', name: '_image_preview', className: 'image_preview', value: this.imagePreviewTemplate, inputWidth: 320, inputHeight: 500},
        {type: 'newcolumn'},
        {type: 'input', name: 'id', label: 'ID', readonly: true},
        {type: 'input', name: 'number', label: 'Номер'},
        {type: 'template', name: '_file_loader', value: this.imageLoader, hidden: true},
        {type: 'container', name: 'labelsTemplates', inputWidth: 200, inputHeight: 300},
        {type: 'block', width: 300, blockOffset: 0, offsetTop: 80, list: [
            {type: 'button', name: 'image_load', value: '', className: 'image_loader'},
            {type: 'newcolumn'},
            {type: 'button', name: 'ok', value: 'Ok', offsetTop: 30, offsetLeft: 180}
        ]}
    ]
    var form = this.window.attachForm(config);
    initEvents(form);
    form.templates = {};
    return form;
};

BookletEditorComponent.prototype.populateData = function(entity) {
    this.controller.setEntity(entity);
    this._populateEntity(entity);
};

BookletEditorComponent.prototype.getData = function() {
    var instance = this;
    var customUpdater = {
        image: function(form, entity) {
            var $image = $('#booklet_item_image');
            var data = $image.data();
            if (data.isbase64) {
                entity.image = $image.attr('src');
            }
        },
        listLabels: function(form, entity) {
            var notExcistingTypes = Object.keys(instance.parent.controller.labelTypes);
            for(var labelIndex = 0; labelIndex < entity.listLabels.length; labelIndex++) {
                var label = entity.listLabels[labelIndex];
                notExcistingTypes.splice(notExcistingTypes.indexOf(label.type), 1);
                label.text = $('#' + label.type).val();
            }
            if (notExcistingTypes.length) {
                for(var labelIndex = 0; labelIndex < notExcistingTypes.length; labelIndex++) {
                    var label = instance.parent.controller.bookletItemLabel.createNewEntity();
                    label.type = notExcistingTypes[labelIndex];
                    label.text = $('#' + notExcistingTypes[labelIndex]).val();
                    entity.listLabels.push(label);
                }
            }
        }
    }
    var entity = this.controller.getEntity();
    components.updateEntity(this.form, entity, customUpdater);
    return entity;
};

BookletEditorComponent.prototype.close = function() {
    this.window.close();
    this.clear();
};

BookletEditorComponent.prototype._populateEntity = function(entity) {
    var instance = this;
    var customUpdater = {
        image: function(form, entity) {
            if (entity.image && entity.image.length > 0) {
                var $image = $('#booklet_item_image');
                if (entity.image.indexOf('data:image') != 0) {
                    $image.attr('src', formatBookletImage(entity.image));
                } else {
                    $image.data('isbase64', true);
                    $image.attr('src', entity.image);
                }
            }
        },
        listLabels: function(form, entity) {
            if (entity.listLabels) {
                for(var labelIndex = 0; labelIndex < entity.listLabels.length; labelIndex++) {
                    var label = entity.listLabels[labelIndex];
                    if (U.hasContent(label.type) && U.hasContent(label.text)) {
                        var matches = /(.*){1}_\d{1}$/.exec(label.type);
                        if (matches && matches.length > 1) {
                            instance.labelTemplates._select_mark(matches[1], true);
                            $('#' + label.type).val(label.text);
                        }
                    }
                }
            }
        }
    }
    components.updateFormData(this.form, entity, customUpdater);
};

BookletEditorComponent.prototype._updateImageTextareas = function(ids) {
    ids = dhx.isArray(ids)? ids: [ids];
    $('.text_fields_sub_container').hide();
    for (var idIndex = 0; idIndex < ids.length; idIndex++) {
        if (ids[idIndex] != '') {
            $('.text_fields_sub_container.' + ids[idIndex]).show();
        }
    }
};

BookletEditorComponent.prototype._initTemplates = function(form) {
    var instance = this;
    var formKey = 'image_inputs_templates';
    var editorFormDataView = new dhtmlXDataView({
        container: this.form.getContainer('labelsTemplates'),
        select: 'multiselect',
        type:{
            template:"<div><img src='#img#'/></div>",
            width: 60,
            height: 100
        }
    });
    $(editorFormDataView.$view).addClass('booklet_item_data_view');
    var templates = [
        {id: 'top_right', img: ''},
        {id: 'bottom_right', img: ''},
        {id: 'bottom_center', img: ''}
    ];

    for (var templateIndex = 0; templateIndex < templates.length; templateIndex++) {
        editorFormDataView.add(templates[templateIndex], 0);
    }
    editorFormDataView.attachEvent('onSelectChange', function(ids) {
        instance._updateImageTextareas(this.getSelected());
    })
/*    editorFormDataView.attachEvent('onBeforeSelect', function(id) {
        if (!editorFormDataView.isSelected(id)) {
            editorFormDataView.select(id);
        } else {
            editorFormDataView.unselect(id);
        }
    })*/
    instance._updateImageTextareas([]);
    return editorFormDataView;
};

BookletEditorComponent.prototype._getFormData = function(form) {
    var formData = form.getFormData();
    $('#booklet_item_image_preview .booklet_item_image_label').each(
        function() {
            var thizz = $(this);
            formData[thizz.attr('id')] = thizz.val();
        }
    )
    return {};
};

BookletEditorComponent.prototype.clear = function() {
    var instance = this;
    if (this.form) {
        this.form.forEachItem(function(name) {
            var type = instance.form.getItemType(name);
            if (['input'].indexOf(type) >= 0 && name.indexOf('_') != 0) {
                instance.form.setItemValue(name);
            }
        });
        $('#booklet_item_image_preview .booklet_item_image_label').val('');
        $('#booklet_item_image').attr('src', '').removeData('isbase64');
        this._resetImageLoader();
        this.labelTemplates.unselectAll();
    }
    this.controller.clear();
};

BookletEditorController = function() {}

BookletEditorController.prototype.init = function() {
    this.id = undefined;
    this.entity = undefined;
};

BookletEditorController.prototype.setEntity = function(entity) {
    this.entity = entity;
};

BookletEditorController.prototype.getEntity = function() {
    return this.entity;
};

BookletEditorController.prototype.clear = function() {
    this.entity = {};
};

BookletEditorController.prototype.updateData = function() {

};

function formatBookletImage(src) {
    if (src && src.length > 0 && src.indexOf('data:image') != 0) {
        src = "/" + app.bookletImageRoot + "/" + src;
    }
    return src;
}

function evaluateSizeMultiplayer(size) {
    var res = 1;
    res *= Math.ceil(size.width/135);
    res *= Math.ceil(size.height/225);
    return res;
}

function initHandlebarsTemplates() {
    Handlebars.registerHelper('sizeHandler', function(size) {
        return Math.ceil(size.width/135) * Math.ceil(size.height/225);
    })
    U.addHandlebarScript('bookletItem', '\
        <div id="{{id}}" class="item x{{sizeHandler size}}">\
            <div class="content">{{> bookletItemContent}}</div>\
            <div class="editors">\
                <div class="edit_item btn">Edit</div>\
                <div class="clear_item btn">Clear</div>\
                <div class="delete_item btn">Del</div>\
            </div>\
            <div class="connectors"></div>\
        </div>');
    Handlebars.registerPartial('bookletItem', $("#bookletItem").html());
    U.addHandlebarScript('bookletItemContent', '\
        <img class="item_image" src="{{renderItemImage image}}"/>\
        <div class="number" style="{{renderItemNumberVisible number}}">{{number}}</div>\
        {{itemLabelGroup listLabels}}');
    U.addHandlebarScript('itemLabelGroup', '\
        {{#each groups}}\
            <div class="label_group {{@key}}_group">\
            {{#each this}}\
                <div id="{{id}}" class="label {{type}}"><pre>{{text}}</pre></div>\
                {{#equal @index 0}}<div class="label labels_delimiter"></div>{{/equal}}\
            {{/each}}\
            </div>\
        {{/each}}');
    U.addHandlebarScript('itemLabel', '<div id="{{id}}" class="label {{type}}"><pre>{{text}}</pre></div>');
    Handlebars.registerHelper('itemLabelGroup', function(labels) {
        var groups = {};
        if (labels.length) {
            for (var labelIndex = 0; labelIndex < labels.length; labelIndex++) {
                var label = labels[labelIndex];
                var labelType = /^(.*)_\d{1}$/.exec(label.type);
                if (labelType.length > 1 && U.hasContent(label.text)) {
                    groups[labelType[1]] = groups[labelType[1]] || [];
                    groups[labelType[1]].push(label);
                }
            }
            if (U.hasContent(groups)) {
                return new Handlebars.SafeString(Handlebars.compile($('#itemLabelGroup').html())({groups: groups}));
            }
        }
        return '';
    })
    Handlebars.registerHelper('renderItemImage', function(image) {
        return image? (image.indexOf('data:image') == 0? image: '/' + app.bookletImageRoot + '/' + image ): "";
    });
    Handlebars.registerHelper('renderItemNumberVisible', function(number) {
        return !number? "display: none;": "";
    });
    Handlebars.registerHelper('renderBookletBorders', function(templateName) {
        if (templateName) {
            return Handlebars.compile($("#" + templateName).html())();
        }
        return '';
    });
    Handlebars.registerHelper('backgroundImage', function(imagePath) {
        return imagePath || '';
    });
    Handlebars.registerPartial("itemLabel", $("#itemLabel").html());
    Handlebars.registerPartial("bookletItemContent", $("#bookletItemContent").html());

    U.addHandlebarScript('previewContainer', '\
        <div id="preview_container" class="preview">\
            <div class="viewport_container">\
                <div class="viewport_sub">\
                    <div class="background"><img src="{{backgroundImage backgroundImage}}"/></div>\
                    <div class="borders">{{> 3c2c}}</div>\
                    <div class="palls"></div>\
                    <div class="viewport"></div>\
                    <div id="zoom_slider"></div>\
            </div>\
        </div>');
    U.addHandlebarScript('fullpreviewContainer', '\
        <div id="preview_container" class="full_preview">\
            <div class="viewport_container">\
                <div class="viewport_sub">\
                    <div class="background"><img src="{{backgroundImage backgroundImage}}"/></div>\
                    <div class="palls"></div>\
                    <div class="viewport">{{#each listItems}}{{> bookletItem}}{{/each}}</div>\
            </div>\
        </div>');
    U.addHandlebarScript('booklet', '<div id="{{id}}" class="booklet"></div>');
    U.addHandlebarScript('3c2c', '\
        <div class="vertical-border-1 item-border horizontal highlight" style="display: none"></div>\
        <div class="vertical-border-2 item-border horizontal highlight" style="display: none"></div>\
        <div class="horizontal-border-1 item-border vertical highlight" style="display: none"></div>\
        <div class="horizontal-border-2 item-border vertical highlight" style="display: none"></div>\
    ');
    Handlebars.registerPartial("3c2c", $("#3c2c").html());
}

bookletBackgroundWindow = (function() {
    var selectCallbacks = [];
    var win = null;
    var form = null;
    var dataView = null;
    var bookletBackgroundWindowInstance = null;
    var selected = null;

    function unselectAll() {
        selected = null;
        if (dataView) {
            dataView.unselectAll();
        }
    }

    function initForm(window) {
        var formConfig = [
            {type: 'container', name: 'images', inputWidth: 730, inputHeight: 410},
            {type: 'block', blockOffset: 7, width: 730, list: [
                {type: 'button', name: 'cancel', value: 'Закрыть'},
                {type: 'newcolumn'},
                {type: 'button', name: 'ok', value: 'Выбрать', offsetLeft: 520}
            ]}
        ];
        var form = window.attachForm(formConfig);
        form.attachEvent('onButtonClick', function(name) {
            if (name == 'ok') {
                var selectedId = dataView.getSelected();
                if (U.hasContent(selectedId)) {
                    selected = dataView.get(selectedId);
                }

            } if (name == 'cancel') {
                unselectAll();
            }
            bookletBackgroundWindowInstance.hide();
        });
        return form;
    }

    function initDataView(container) {
        var view = new dhtmlXDataView({
            container: container,
            drag: false,
            select: true,
            template: "<img class='booklet_select_preview' src='#image#'/>"
        });
        serviceWrapper.getBookletBackgrounds(function(backgrounds) {
            if (backgrounds && backgrounds.length) {
                backgrounds.forEach(function(image) {
                    view.add({id: U.getRandomString(), image: "/" + image});
                })
            }
            //console.info(backgrounds);
        });
        return view;
    }

    function closeCallback() {
        return function() {
            selectCallbacks.forEach(function(_callback) {
                _callback(selected);
            })
        }
    }

    return {
        show: function() {
            bookletBackgroundWindowInstance = this;
            if (!win) {
                win = components.initDhtmlxWindow({
                    width: 740,
                    height: 500
                }, closeCallback());
                form = initForm(win);
                dataView = initDataView(form.getContainer('images'));
            }
            win.show();
        },
        hide: function() {
            bookletBackgroundWindowInstance = this;
            win.close();
        },
        addSelectCallback: function(callback) {
            bookletBackgroundWindowInstance = this;
            if (typeof callback == 'function') {
                selectCallbacks.push(callback);
            }
            unselectAll();
        }
    }
})()

function generateId(id) {
    return U.getRandomString();
}
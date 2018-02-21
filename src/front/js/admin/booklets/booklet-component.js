define([
    'booklets/booklet-editor-controller',
    'booklets/booklet-editor-component',
    'booklets/booklet-item-component'
], function (BookletEditorController, BookletEditorComponent, BookletItem) {
    'use strict';

     function BookletComponent(domParentContainer, controller) {
        this.$domParentContainer = $(domParentContainer);
        this.$bookletDOM = null;
        this.$bookletItemsDOM = null;
        this.listeners = {};
        this.bookletItems = [];
        this.controller = controller;
        this.controller.owner = this;
        this.items = [];
        this.editorWindow = undefined;
        this.template = '3c2c';
        this.itemSizeCode = '135x225';
        this.initGridRules();
        this.initSizeRules();
    }

    BookletComponent.prototype.init = function () {
        var editorWindowController = new BookletEditorController();
        this.editorWindow = new BookletEditorComponent(this).init(editorWindowController);

        //this.enable(false);
        return this;
    };

    BookletComponent.prototype.render = function () {
        var entity = this.controller.getEntity();
        if (!this.$bookletDOM) {
            this.$bookletDOM = U.compilePrefillHandlebar("previewContainer", entity);
            this.$domParentContainer.append(this.$bookletDOM);
            this.$bookletDOM = $(this.$bookletDOM);
            this.gridRules[this.template].preRender();
            this.$bookletItemsDOM = $('.viewport', this.$bookletDOM);
        } else {
            this.renderBorders(entity.bordersTemplate);
            this.renderPalls();
        }
        $('.viewport', this.$bookletDOM).html('');
        if (entity.listItems && entity.listItems.length) {
            var bookletInstance = this;
            entity.listItems.forEach(function (bookletItemEntity) {
                var bookletItem = new BookletItem(bookletInstance, bookletInstance.$bookletItemsDOM, bookletInstance.editorWindow).init(bookletItemEntity);
                bookletInstance.bookletItems.push(bookletItem);
                bookletItem.render();
            })
        }
    };

    BookletComponent.prototype.updateBackground = function (image) {
        this.controller.updateBackground(image);
        $('.background img', this.$bookletDOM).attr('src', image);
    };

    BookletComponent.prototype.renderBorders = function (bordersTemplate) {
        $('.borders', this.$bookletDOM).html(Handlebars.compile($('#borders-default').html())());
    };

    BookletComponent.prototype.renderPalls = function () {

    };

    BookletComponent.prototype.updateTemlate = function (template) {
        var borders = $('.borders', this.$bookletDOM);
        borders.empty();
        this.gridRules[this.template].preRender();
        borders.html(borders.html() + Handlebars.compile($('#borders-default').html())());
    };


    BookletComponent.prototype.prepareItem = function (item, data) {
        var result = item;
        if (typeof data != 'undefined' && Object.keys(data).length) {
            for (var key in data) {
                result = result.replace(new RegExp('({{' + key + '}})+'), U.hasContent(data[key]) ? data[key] : '');
            }
        }
        return result;
    };

    BookletComponent.prototype.clearTemplate = function (template) {
        if (typeof template == 'string') {
            return template.replace(/({{[^}]*}})+/g, '');
        }
        return '';
    };

    BookletComponent.prototype.enable = function (state) {
        state = typeof state == 'undefined' ? true : state;
        this.$bookletDOM.removeClass(state ? 'disable' : 'enable').addClass(state ? 'enable' : 'disable');
        return this;
    };

    BookletComponent.prototype.showEditor = function (itemId) {
        this.editorWindow.show();
        this.editorWindow.populateData(this.controller.horizontalEntity[itemId]);
    };

    BookletComponent.prototype.clearItem = function (id) {
        var entity = this.controller.clearItem(id);
        this.render(entity);
    };

    BookletComponent.prototype.clear = function () {
        this.$domParentContainer.html("");
        this.$bookletDOM = null;
        this.$bookletItemsDOM = null;
        this.controller.clear();
        if (this.bookletItems && this.bookletItems.length) {
            this.bookletItems.forEach(function (value, index) {
                value.unload();
            })
        }
        this.bookletItems = [];
        return this;
    };

    BookletComponent.prototype.save = function (callback) {
        this.controller.save(callback);
    };

    BookletComponent.prototype.delete = function (callback) {
        this.controller.delete(callback);
    };

    BookletComponent.prototype.populate = function (entity) {
        app.layout.progressOn();
        this.clear();
        this.controller.setEntity(entity);
        this.propertyChange('state', [entity]);
        this.render(entity);
        app.layout.progressOff();
        return entity;
    };

    BookletComponent.prototype.createNewBooklet = function () {
        this.clear();
        var entity = this.controller.createNewBooklet();
        /*    this.propertyChange('state', [entity]);*/
        this.render(entity);
        return entity;
    };

    BookletComponent.prototype.canCreateNew = function () {
        return this.controller.canCreateNew();
    };

    BookletComponent.prototype.initGridRules = function () {
        var inst = this;
        var common = {
            x: function (x, width) {
                var cfg = inst.gridRules[inst.template]._additional;
                return this._additional.xyHandle(
                    x, width,
                    cfg.xLeft,
                    cfg.xRight,
                    cfg.xMagnetize
                );
            },
            y: function (y, height) {
                var cfg = inst.gridRules[inst.template]._additional;
                return this._additional.xyHandle(
                    y, height,
                    cfg.yTop,
                    cfg.yBottom,
                    cfg.yMagnetize
                );
            },
            highLightX: function (leftX, rightX) {
                var cfg = inst.gridRules[inst.template]._additional;
                var $verticalTop = $('.vertical-border-1');
                var $verticalBottom = $('.vertical-border-2');

                $verticalTop.finish();
                $verticalTop.css('left', (leftX ? leftX : 0) + 'px');
                $verticalTop[cfg.xLeft.indexOf(leftX) > -1 ? 'show' : 'hide'](100);

                $verticalBottom.finish();
                $verticalBottom.css('left', (rightX ? rightX : 0) + 'px');
                $verticalBottom[cfg.xRight.indexOf(rightX) > -1 ? 'show' : 'hide'](100);
            },
            highLightY: function (topY, bottomY) {
                var cfg = inst.gridRules[inst.template]._additional;
                var $horizontalLeft = $('.horizontal-border-1');
                var $horizontalRight = $('.horizontal-border-2');
                $horizontalLeft.finish();
                $horizontalLeft.css('top', (topY ? topY : 0) + 'px');
                $horizontalLeft[cfg.yTop.indexOf(topY) > -1 ? 'show' : 'hide'](100);

                $horizontalRight.finish();
                $horizontalRight.css('top', (bottomY ? bottomY : 0) + 'px');
                $horizontalRight[cfg.yBottom.indexOf(bottomY) > -1 ? 'show' : 'hide'](100);
            },
            preRender: function () {
                var cfg = inst.gridRules[inst.template]._additional;
                var $container = $('#preview_container .borders');
                var horBordersPositions = cfg.yTop.concat(cfg.yBottom);
                var verBordersPositions = cfg.xLeft.concat(cfg.xRight);
                horBordersPositions.forEach(function (position) {
                    var border = document.createElement('DIV');
                    border.setAttribute('class', 'vertical-border shadow-item-border item-border vertical');
                    border.style.top = position + 'px';
                    $container.prepend(border);
                });
                verBordersPositions.forEach(function (position) {
                    var border = document.createElement('DIV');
                    border.setAttribute('class', 'horizontal-border shadow-item-border item-border horizontal');
                    border.style.left = position + 'px';
                    $container.prepend(border);
                });
            },
            clearHightLight: function () {
                $('.vertical-border-1, .vertical-border-2, .horizontal-border-1 ,.horizontal-border-2').hide();
            }
        };
        this.gridRules = {
            '3c2c': {
                x: common.x,
                y: common.y,
                highLightX: common.highLightX,
                highLightY: common.highLightY,
                preRender: common.preRender,
                clearHightLight: common.clearHightLight,
                _additional: {
                    xyHandle: function (pos, size, poss1, poss2, magnetize) {
                        var pos1 = pos;
                        var pos2 = pos + size;
                        pos1 = this.checkCoordinates(pos1, poss1, magnetize);
                        pos2 = this.checkCoordinates(pos2, poss2, magnetize);
                        return [pos1, pos2];
                    },
                    checkCoordinates: function (pos, poss, magnetize) {
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
            },
            '2c3c': {
                x: common.x,
                y: common.y,
                highLightX: common.highLightX,
                highLightY: common.highLightY,
                preRender: common.preRender,
                clearHightLight: common.clearHightLight,
                _additional: {
                    xyHandle: function (pos, size, poss1, poss2, magnetize) {
                        var pos1 = pos;
                        var pos2 = pos + size;
                        pos1 = this.checkCoordinates(pos1, poss1, magnetize);
                        pos2 = this.checkCoordinates(pos2, poss2, magnetize);
                        return [pos1, pos2];
                    },
                    checkCoordinates: function (pos, poss, magnetize) {
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
                    xRight: [157, 314, 471, 658, 815, 972],
                    xLeft: [22, 179, 336, 523, 680, 837],
                    yTop: [15, 245, 475],
                    yBottom: [240, 470, 700]
                }
            }
        }
    };

    BookletComponent.prototype.initSizeRules = function () {
        this.sizeRules = {
            '135x225': {
                width: function (width) {
                    return Math.min(
                        Math.round(width / this._additional.basicWidth) * this._additional.basicWidth || this._additional.basicWidth,
                        this._additional.maxWidth);
                },
                height: function (height) {
                    return Math.min(
                        Math.round(height / this._additional.basicHeight) * this._additional.basicHeight || this._additional.basicHeight,
                        this._additional.maxHeight);
                },
                _additional: {
                    basicWidth: 135,
                    basicHeight: 225,
                    maxWidth: 135 * 2,
                    maxHeight: 225 * 3
                }
            }
        }
    };

    BookletComponent.prototype.getTemplateTypes = function () {
        return {
            '3c2c': '3c2c',
            '2c3c': '2c3c'
        }
    };

    BookletComponent.prototype.setTemplate = function (template) {
        if (this.getTemplateTypes().hasOwnProperty(template)) {
            this.template = template;
            this.updateTemlate(this.template);
        } else {
            console.warn('There is now such template "%s"', template);
        }
    };

    BookletComponent.prototype.checkPosToSnapX = function (x, width) {
        var xSnap = this.gridRules[this.template].x(x, width);
        return xSnap;
    };

    BookletComponent.prototype.checkPosToSnapY = function (y, height) {
        var ySnap = this.gridRules[this.template].y(y, height);
        return ySnap;
    };

    BookletComponent.prototype.highLightX = function (xLeft, xRight) {
        this.gridRules[this.template].highLightX(xLeft, xRight);
    };

    BookletComponent.prototype.highLightY = function (yTop, yBottom) {
        this.gridRules[this.template].highLightY(yTop, yBottom);
    };

    BookletComponent.prototype.clearHightLight = function () {
        this.gridRules[this.template].clearHightLight();
    };

    BookletComponent.prototype.addNewItem = function () {
        var item = new BookletItem(this, this.$bookletItemsDOM, this.editorWindow).createNew().render();
        this.bookletItems.push(item);
    };

    BookletComponent.prototype.deleteItem = function () {
        if (arguments.length && U.hasContent(arguments[0])) {
            var bookletInstance = this;
            var parameter = arguments[0];
            // it's mean id
            if (typeof parameter == 'number') {
                this.bookletItems.forEach(function (item, index) {
                    if (item.id == parameter) {
                        this.splice(index, 1);
                        return false;
                    }
                })
            }
            // it's mean item instance
            else if (U.isObject(arguments)) {
                this.bookletItems.forEach(function (item, index) {
                    if (item == parameter) {
                        bookletInstance.bookletItems.splice(index, 1);
                        return false;
                    }
                })
            }
        }
    };

    BookletComponent.prototype.isNew = function () {
        return !!this.controller.entity._isNew;
    };

    BookletComponent.prototype.isEmpty = function () {
        return !U.hasContent(this.controller.entity);
    };

    BookletComponent.prototype.getItemSize = function (id) {
        return this.controller.horizontalEntity[id].size;
    };

    BookletComponent.prototype.updateConnectors = function (id) {
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
                    1: function (rowInd, colInd, data) {
                        matrix[rowInd][colInd].top = findPalls(rowInd - 1, colInd, 1, 'horizontal');
                        matrix[rowInd][colInd].right = findPalls(rowInd, colInd + 1, 1, 'vertical');
                        matrix[rowInd][colInd].bottom = findPalls(rowInd + 1, colInd, 1, 'horizontal');
                        matrix[rowInd][colInd].left = findPalls(rowInd, colInd - 1, 1, 'vertical');
                        matrix[rowInd][colInd].isEmpty = false;
                        matrix[rowInd][colInd].data = data;
                        return true;
                    },
                    2: function (rowInd, colInd, data) {
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
                };
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
                if ($neighbors[neiIndex].id === id) {
                    idIndex = neiIndex;
                }
            }
            //find id position
            if (idIndex === -1) {
                return null;
            }
            /*if ()*/
            return {top: 0, right: 0, bottom: 0, left: 0};
        }

        buildMatrix(this.controller.entity.listColumns[0].listItems);
    };

    BookletComponent.prototype.updateBorder = function () {

    };

    return BookletComponent;
});
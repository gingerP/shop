define([
    'booklets/booklet-item-controller'
], function (BookletItemController) {
    'use strict';

    function BookletItem(booklet, $parentContainer, editor) {
        this.booklet = booklet;
        this.$parentContainer = $parentContainer;
        this.controller = new BookletItemController();
        this.controller.owner = this;
        this.editor = editor;
    }

    BookletItem.prototype.init = function (entity) {
        this.controller.setEntity(entity);
        return this;
    };

    BookletItem.prototype.createNew = function () {
        var entity = this.controller.createNew();
        return this;
    };

    BookletItem.prototype.render = function () {
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

    BookletItem.prototype.getId = function () {
        var entity = this.controller.getEntity();
        return entity ? entity.id : '';
    };

    BookletItem.prototype.initEvents = function () {
        var itemInstance = this;
        this.$itemDOM.on('click', '.edit-item', function () {
            itemInstance.editor.updateRelations(itemInstance);
            itemInstance.editor.show();
            itemInstance.editor.populateData(itemInstance.controller.getEntity());
        });
        this.$itemDOM.on('click', '.clear-item', function () {
            itemInstance.clear();
        });
        this.$itemDOM.on('click', '.delete-item', function () {
            itemInstance.booklet.deleteItem(itemInstance);
            itemInstance.unload();
        });

        this.draggable = undefined;
        if (U.hasContent(this.getId())) {
            var selector = "#" + this.getId();
            this.resizable = $(selector).resizable({
                delay: 0,
                resize: function (event, ui) {
                    ui.size.width = itemInstance.booklet.sizeRules[itemInstance.booklet.itemSizeCode].width(ui.size.width);
                    ui.size.height = itemInstance.booklet.sizeRules[itemInstance.booklet.itemSizeCode].height(ui.size.height);
                    itemInstance.controller.updateEntity({size: {width: ui.size.width, height: ui.size.height}});
                }
            }).on('mouseup', function () {
                itemInstance.booklet.clearHightLight();
            });

            $('.ui-resizable-handle', this.resizable).attr('data-clickable', true);
            this.draggable = Draggable.create(selector, {
                type: "x,y",
                edgeResistance: 0.5,
                autoScroll: 0.5,
                bounds: this.$parentContainer,
                lockedAxis: true,
                throwProps: true,
                snap: {
                    x: function (endValue) {
                        return endValue;
                    },
                    y: function (endValue) {
                        return endValue;
                    }
                },
                liveSnap: {
                    x: function (end) {
                        var poss = itemInstance.booklet.checkPosToSnapX(end, itemInstance.controller.getSize().width);
                        itemInstance.booklet.highLightX(poss[0], poss[1]);
                        var res = U.hasContent(poss[0]) ? poss[0] : end;
                        itemInstance.controller.updateEntity({position: {x: res}});
                        return res;
                    },
                    y: function (end) {
                        var poss = itemInstance.booklet.checkPosToSnapY(end, itemInstance.controller.getSize().height);
                        itemInstance.booklet.highLightY(poss[0], poss[1]);
                        var res = U.hasContent(poss[0]) ? poss[0] : end;
                        itemInstance.controller.updateEntity({position: {y: res}});
                        return res;
                    }
                },
                onDragEnd: function () {
                    itemInstance.booklet.clearHightLight();
                }
            });
        }
    };

    BookletItem.prototype.clear = function () {
        this.controller.clear();
        this.render();
    };

    BookletItem.prototype.unload = function () {
        this.$parentContainer = null;
        this.controller = null;
        this.editor = null;
        if (this.$itemDOM) {
            this.$itemDOM.remove();
        }
        this.draggable = null;
        this.resizable = null;
        this.booklet.clearHightLight();
        this.booklet = null;
    };

    BookletItem.prototype.updateSize = function () {
        if (this.$itemDOM) {
            var size = this.controller.getSize();
            this.$itemDOM.css('width', size.width);
            this.$itemDOM.css('height', size.height);
        }
    };

    BookletItem.prototype.updatePosition = function () {
        if (this.$itemDOM) {
            var position = this.controller.getPosition();
            TweenLite.to(this.$itemDOM, 0, {
                x: position.x,
                y: position.y
            });
        }
    };

    return BookletItem;
});
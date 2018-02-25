define([
    'common/dialog',
    'booklets/booklet-item-controller'
], function (Dialog, BookletItemController) {
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
            this.updatePosition();
        }
        return this;
    };

    BookletItem.prototype.getId = function () {
        var entity = this.controller.getEntity();
        return entity ? entity.id : '';
    };

    BookletItem.prototype.initEvents = function () {
        var self = this;
        self.$itemDOM.on('click', '.edit-item', function () {
            self.editor.updateRelations(self);
            self.editor.show();
            self.editor.populateData(self.controller.getEntity());
        });
        self.$itemDOM.on('click', '.clear-item', function () {
            Dialog.confirm('Вы уверены, что хотитеть очистить содержимое?')
                .then(function () {
                    self.clear();
                });
        });
        self.$itemDOM.on('click', '.delete-item', function () {
            Dialog.confirm('Вы уверены, что хотите удалить весь элемент?')
                .then(function () {
                    self.booklet.deleteItem(self);
                    self.unload();
                });
        });

        self.draggable = null;
        if (U.hasContent(self.getId())) {
            var selector = "#" + self.getId();
            self.resizable = $(selector).resizable({
                delay: 0,
                resize: function (event, ui) {
                    var sizeRule = self.booklet.sizeRules[self.booklet.itemSizeCode];
                    ui.size.width = sizeRule.width(ui.size.width);
                    ui.size.height = sizeRule.height(ui.size.height);
                    self.controller.updateEntity({size: {width: ui.size.width, height: ui.size.height}});
                }
            }).on('mouseup', function () {
                self.booklet.clearHightLight();
            });

            $('.ui-resizable-handle', self.resizable).attr('data-clickable', true);
            self.draggable = Draggable.create(selector, {
                type: 'x,y',
                edgeResistance: 0.5,
                autoScroll: 0.5,
                bounds: self.$parentContainer,
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
                        var poss = self.booklet.checkPosToSnapX(end, self.controller.getSize().width);
                        self.booklet.highLightX(poss[0], poss[1]);
                        var res = U.hasContent(poss[0]) ? poss[0] : end;
                        self.controller.updateEntity({position: {x: res}});
                        return res;
                    },
                    y: function (end) {
                        var poss = self.booklet.checkPosToSnapY(end, self.controller.getSize().height);
                        self.booklet.highLightY(poss[0], poss[1]);
                        var res = U.hasContent(poss[0]) ? poss[0] : end;
                        self.controller.updateEntity({position: {y: res}});
                        return res;
                    }
                },
                onDragEnd: function () {
                    self.booklet.clearHightLight();
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
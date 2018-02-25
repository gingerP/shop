define([
    'common/service-entities'
], function (ServiceEntities) {
    'use strict';

    function generateId() {
        return U.getRandomString();
    }

    function BookletItemController() {
        this.entity = null;
        this.itemTypes = {
            item: 'item',
            label: 'label'
        };
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
        };
        var bookletItemLabelEntity = {
            id: null,
            type: null,
            text: null,
            itemType: this.itemTypes.label
        };

        this.clearItemTemplate = {
            image: null,
            number: null
        };
        this.clearLabelTemplate = {
            text: null
        };
        this.bookletItemManager = new ServiceEntities().init(bookletItemEntity, 'id', 999);
        this.bookletItemLabelManager = new ServiceEntities().init(bookletItemLabelEntity, 'id', 999);
        this.bookletItemManager.generateId = generateId;
        this.bookletItemLabelManager.generateId = generateId;
        this.labelTypes = {
            top_right_0: 'top_right_0',
            top_right_1: 'top_right_1',
            top_left_0: 'top_left_0',
            top_left_1: 'top_left_1',
            bottom_right_0: 'bottom_right_0',
            bottom_right_1: 'bottom_right_1',
            bottom_left_0: 'bottom_left_0',
            bottom_left_1: 'bottom_left_1'
        };
    }

    BookletItemController.prototype.createNew = function () {
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

    BookletItemController.prototype.setEntity = function (entity) {
        this.entity = entity;
        this.entity.position.x = isNaN(this.entity.position.x) ? 0 : parseFloat(this.entity.position.x);
        this.entity.position.y = isNaN(this.entity.position.y) ? 0 : parseFloat(this.entity.position.y);
        this.entity.size.width = isNaN(this.entity.size.width) ? 135 : parseFloat(this.entity.size.width);
        this.entity.size.height = isNaN(this.entity.size.height) ? 225 : parseFloat(this.entity.size.height);
    };

    BookletItemController.prototype.updateEntity = function (entity) {
        $.extend(true, this.entity || {}, entity || {});
    };

    BookletItemController.prototype.getEntity = function () {
        return this.entity;
    };

    BookletItemController.prototype.getPosition = function () {
        return this.entity.position;
    };

    BookletItemController.prototype.getSize = function () {
        return this.entity.size;
    };

    BookletItemController.prototype.clear = function () {
        var itemInstance = this;
        $.extend(this.entity, this.clearItemTemplate);
        this.entity.listLabels.forEach(function (label) {
            $.extend(label, itemInstance.clearLabelTemplate);
        });
    };

    return BookletItemController;
});

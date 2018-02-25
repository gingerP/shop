define([
    'common/components',
    'common/services',
    'common/service-entities',
    'common/toast'
], function (Components, Services, ServiceEntities, Toast) {
    'use strict';

    function generateId(id) {
        return U.getRandomString();
    }

    function BookletController() {
    }

    BookletController.prototype.init = function () {
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
        };
        this.bookletManager = new ServiceEntities().init(bookletEntity, 'id', 1, {
            created: Date.now,
            updated: Date.now
        });
        this.bookletManager.generateId = generateId;

        return this;
    };

    BookletController.prototype.setEntity = function (entity) {
        this.entity = entity;
    };

    BookletController.prototype.createNewBooklet = function () {
        this.entity = this.bookletManager.createNewEntity();
        this.entity._isNew = true;
        return this.entity;
    };

    BookletController.prototype.updateHorizontalEntity = function (booklet) {
    };

    BookletController.prototype.getEntity = function () {
        return this.entity;
    };

    BookletController.prototype.updateBackground = function (backgroundImage) {
        this.entity.backgroundImage = backgroundImage;
    };

    BookletController.prototype.updateEntity = function () {
        this.entity.listItems = [];
        this.entity.bordersTemplate = this.owner.template;
        var instance = this;
        if (this.owner.bookletItems && this.owner.bookletItems.length) {
            this.owner.bookletItems.forEach(function (bookletItem) {
                instance.entity.listItems.push(bookletItem.controller.getEntity());
            });
        }
        return this.entity;
    };

    BookletController.prototype.canCreateNew = function () {
        return true;
    };

    BookletController.prototype.load = function (id, callback) {
        var instance = this;
        Services.getBooklet(id)
            .then(function (data) {
                instance.owner.clear();
                instance.owner.controller.setEntity(data);
                instance.owner.render();
                if (data.bordersTemplate) {
                    instance.owner.setTemplate(data.bordersTemplate);
                }
                if (typeof callback === 'function') {
                    callback(data);
                }
            })
            .catch(Toast.error);
    };

    BookletController.prototype.save = function (callback) {
        var entity = this.updateEntity();
        var id = entity.id;
        entity.id = entity._isNew ? null : entity.id;
        Components.prepareEntity(entity);
        Services.saveBooklet(entity)
            .then(function (result) {
                callback(id, result);
            })
            .catch(Toast.error);
    };

    BookletController.prototype.delete = function (callback) {
        var instance = this;

        function localCallback() {
            instance.owner.clear();
            instance.clear();
            callback();
        }

        if (!this.entity._isNew) {
            Services.deleteBooklet(this.entity.id)
                .then(localCallback)
                .catch(Toast.error);
        } else {
            localCallback();
        }
    };

    BookletController.prototype.clear = function () {
        this.bookletManager.removeAll();
        this.entity = null;
    };

    BookletController.prototype.clearItem = function (id) {
        this._clearItem(this.horizontalEntity[id]);
        return this.horizontalEntity[id];
    };

    BookletController.prototype._clearItem = function (entity) {
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

    return BookletController;
});

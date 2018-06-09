define([
    'lodash'
], function (_) {
    var ServiceEntities = function ServiceEntities() {
    };

    ServiceEntities.prototype.init = function (entity, entityIdKey, maxEntitiesCount, defaults) {
        this.defaults = defaults || {};
        this.maxEntitiesCount = maxEntitiesCount;
        this.newEntitiesCount = 0;
        this.newEntities = {};
        this.idField = entityIdKey;
        this.entity = entity;
        this.schema = null;
        return this;
    };

    ServiceEntities.prototype.initWithSchema = function (schema, entityIdKey, maxEntitiesCount) {
        this.schema = schema;
        this.defaults = this._getDefaultsFromSchema();
        this.maxEntitiesCount = maxEntitiesCount;
        this.newEntitiesCount = 0;
        this.newEntities = {};
        this.idField = entityIdKey;
        this.entity = this._getDefaultsFromSchema();
        return this;
    };

    ServiceEntities.prototype._getDefaultsFromSchema = function _getDefaultsFromSchema() {
        var defaults = {};
        for(var key in this.schema) {
            if (this.schema.hasOwnProperty(key)) {
                var schemaItem = this.schema[key];
                var type = schemaItem[0];
                var defaultValue = schemaItem[1];
                switch (type) {
                    case Number:
                        defaults[key] = _.isUndefined(defaultValue) ? 0 : defaultValue;
                        break;
                    case String:
                        defaults[key] = _.isUndefined(defaultValue) ? '' : defaultValue;
                        break;
                    case Date:
                        defaults[key] = _.isUndefined(defaultValue) ? new Date() : defaultValue;
                        break;
                    case Array:
                        defaults[key] = _.isUndefined(defaultValue) ? [] : defaultValue;
                        break;
                    case Boolean:
                        defaults[key] = _.isUndefined(defaultValue) ? true : defaultValue;
                        break;
                }
            }
        }
        return defaults;
    };

    ServiceEntities.prototype.createNewEntity = function () {
        var newEntity = JSON.parse(JSON.stringify(this.entity));
        this.newEntitiesCount++;
        newEntity[this.idField] = this.generateId(this.newEntitiesCount);
        this.newEntities[newEntity[this.idField]] = newEntity;
        newEntity._isNew = true;
        for (var key in this.defaults) {
            if (newEntity.hasOwnProperty(key)) {
                newEntity[key] = typeof this.defaults[key] === 'function' ? this.defaults[key]() : this.defaults[key];
            }
        }
        return newEntity;
    };

    ServiceEntities.prototype.removeEntity = function (id) {
        delete this.newEntities[id];
        return this;
    };

    ServiceEntities.prototype.removeAll = function () {
        this.newEntities = {};
        this.newEntitiesCount = 0;
    };

    ServiceEntities.prototype.canCreateNewEntity = function () {
        return this.maxEntitiesCount > this.newEntitiesCount;
    };

    ServiceEntities.prototype.generateId = function (id) {
        return '_new:' + id;
    };

    return ServiceEntities;
});

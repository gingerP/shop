define([], function () {
    var ServiceEntities = function ServiceEntities() {
    };

    ServiceEntities.prototype.init = function (entity, entityIdKey, maxEntitiesCount, defaults) {
        this.defaults = defaults || {};
        this.maxEntitiesCount = maxEntitiesCount;
        this.newEntitiesCount = 0;
        this.newEntities = {};
        this.idField = entityIdKey;
        this.entity = entity;
        return this;
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

define([], function () {
    var Observable = function Observable() {
        this.listeners = {};
    };

    Observable.prototype.addListener = function (propertyName, listener) {
        this.listeners[propertyName] = this.listeners[propertyName] || [];
        this.listeners[propertyName].push(listener);
        return this;
    };

    Observable.prototype.propertyChange = function (propertyName, state, owner) {
        if (typeof propertyName === 'string' && propertyName.length > 1 && this.listeners[propertyName].length) {
            for (var listenerIndex = 0; listenerIndex < this.listeners[propertyName].length; listenerIndex++) {
                var method = 'on' + propertyName[0].toUpperCase() +
                    propertyName.substr(1, propertyName.length) + 'Change';
                var listenerFunction = this.listeners[propertyName][listenerIndex][method];
                if (typeof listenerFunction === 'function') {
                    listenerFunction(state, propertyName, owner);
                }
            }
        }
        return this;
    };
    return Observable;
});
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
        var shouldProceed = typeof propertyName === 'string' && propertyName.length > 1
            && this.listeners[propertyName] && this.listeners[propertyName].length;
        if (shouldProceed) {
            for (var listenerIndex = 0; listenerIndex < this.listeners[propertyName].length; listenerIndex++) {
                var listenerFunction = this.listeners[propertyName][listenerIndex];
                if (typeof listenerFunction === 'function') {
                    listenerFunction(state, propertyName, owner);
                }
            }
        }
        return this;
    };
    return Observable;
});
define([], function () {
    var Handlers = function Handlers(callback) {
        this.callback = callback;
    };

    Handlers.prototype.success = function (data) {
        if (typeof(this.callback) === 'function') {
            this.callback(data);
        }
    };
    return Handlers;
});
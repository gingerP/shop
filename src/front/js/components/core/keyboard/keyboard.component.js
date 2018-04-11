window.keyBoard = (function () {
    var handlers = {};
    $('body').keydown(function (e) {
        if (typeof(handlers[e.keyCode]) === 'function') {
            handlers[e.keyCode]();
        }
    });
    return {
        init: function () {
        },
        handle: function (keyBoardKey, handler) {
            handlers[keyBoardKey] = handler;
        }
    };
})();

'use strict';
define([], function() {
    function error(message, callback) {
        dhtmlx.alert({
            title: 'Alert',
            type: 'alert-error',
            text: '<span style="word-break: break-all">' + message + '</span>',
            callback: callback
        });
    }

    return {
        error: error
    }
});
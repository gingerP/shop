'use strict';
define([
    'common/services'
], function (Services) {
    function getStackIfExist(error) {
        var stack = '';
        if (typeof error.stack === 'string') {
            stack += '<br><div style="min-width: 500px;text-align: left;">' + error.stack.replace(/\n/g, '<br>') + '</div>';
        }
        return stack;
    }

    function error(error) {
        var message = 'Unknown error';
        if (typeof error === 'string') {
            message = error;
        } else if (error.message) {
            message = error.message;
        } else if (error.response && error.response.statusText) {
            message = error.response.statusText;
        }

        Services.saveError(message, typeof error.stack === 'string' ? error.stack : '');

        message = '<span style="font-weight: bold;">' + message + '</span>';
        message += getStackIfExist(error);
        dhtmlx.alert({
            title: 'Alert',
            type: 'alert-error',
            text: '<span style="word-break: break-all">' + message + '</span>'
        });
        console.error(error);
    }

    return {
        error: error
    }
});
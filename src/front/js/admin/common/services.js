define([
    'axios',
    'common/handlers'
], function (axios, Handlers) {

    function failed(error) {
        var message = 'Unknown error';
        if (typeof error === 'string') {
            message = error;
        } else if (error.response && error.response.data && error.response.data.message) {
            message = error.response.data.message;
        } else if (error.message) {
            message = error.message;
        } else if (error.response && error.response.statusText) {
            message = error.response.statusText;
        } else {
            message = error.responseJSON && error.responseJSON.message || error.statusText;
        }
        api.saveError(message, typeof error.stack === 'string' ? error.stack : '');
        dhtmlx.alert({
            title: 'Alert',
            type: 'alert-error',
            text: '<span style="word-break: break-all">' + message + '</span>',
            callback: function callback() {
                if (error.status === 401) {
                    document.location.reload(true);
                }
            }
        });
        console.error(error);
    }

    function load(apiMethod, params, handlers) {
        return axios({
            method: 'post',
            data: params || {},
            dataType: 'json',
            headers: {contentType: 'application/json;charset=utf-8'},
            url: '/api/' + apiMethod
        }).then(function (data) {
                if (handlers && typeof(handlers.success) == 'function') {
                    handlers.success(data);
                }
                return data;
            }
        ).catch(failed);
    }

    function getData(response) {
        return response && response.data;
    }

    var api = {
        saveError: function(message, stack) {
            return load('saveError', {message: message, stack: stack, pageUrl: window.location.href}).then(getData);
        },
        getAddresses: function (id, callback) {
            return load('getAddresses', {id: -1}, new Handlers(callback)).then(getData);
        },
        getGoods: function (id) {
            return load('getGoods', {id: -1}).then(getData);
        },
        saveGoodsOrder: function (data, callback) {
            return load('saveOrder', data, new Handlers(callback)).then(getData);
        },
        getNextGoodCode: function (code, callback) {
            return load('getNextGoodCode', {code: code}, new Handlers(callback)).then(getData);
        },
        getDescriptionKeys: function (callback) {
            return load('getDescriptionKeys', null, new Handlers(callback)).then(getData);
        },
        getPrices: function (callback) {
            return load('getPrices', null, new Handlers(callback)).then(getData);
        },
        getGoodsKeys: function (callback) {
            return load('getGoodsKeys', null, new Handlers(callback)).then(getData);
        },
        updateGood: function (id, data, callback) {
            return load('updateGood', {id: id, data: data}, new Handlers(callback)).then(getData);
        },
        getGood: function (id, callback) {
            return load('getGood', {id: id}, new Handlers(callback)).then(getData);
        },
        getGoodsAdminOrder: function (callback) {
            return load('getAdminOrder', null, new Handlers(callback)).then(getData);
        },
        getGoodImages: function (id, callback) {
            return load('getGoodImages', {id: id}, new Handlers(callback)).then(getData);
        },
        deleteGood: function (id, callback) {
            return load('deleteGood', {id: id}, new Handlers(callback)).then(getData);
        },
        uploadImagesForGood: function (id, data, callback) {
            return load('uploadImagesForGood', {id: id, data: data}, new Handlers(callback)).then(getData);
        },
        updatePrices: function (data, callback) {
            return load('updatePrices', {data: data}, new Handlers(callback)).then(getData);
        },
        readImagesFromCatalogToDb: function readImagesFromCatalogToDb() {
            return load('readImagesFromCatalogToDb').then(getData);
        },
        getAdminSettings: function getAdminSettings() {
            return load('getAdminSettings').then(getData);
        },
        /*****************************************Booklets*************************************/
        listBooklets: function (mapping, callback) {
            return load('listBooklets', {mapping: mapping}, new Handlers(callback)).then(getData);
        },
        getBooklet: function (id, mapping, callback) {
            return load('getBooklet', {id: id, mapping: mapping}).then(getData);
        },
        saveBooklet: function (data, callback) {
            return load('saveBooklet', {data: data}, new Handlers(callback)).then(getData);
        },
        deleteBooklet: function (id, callback) {
            return load('deleteBooklet', {id: id}, new Handlers(callback)).then(getData);
        },
        getBookletBackgrounds: function (callback) {
            return load('getBookletBackgrounds', null, new Handlers(callback)).then(getData);
        }
    };
    return api;
});
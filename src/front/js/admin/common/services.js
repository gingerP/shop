define([
    'jquery',
    'common/handlers'
], function ($, Handlers) {
    function failed(error) {
        var message = error.responseJSON && error.responseJSON.message || error.statusText || 'Неизвестная ошибка';
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
    }

    function load(apiMethod, params, handlers) {
        return $.ajax({
            type: 'POST',
            data: JSON.stringify(params || {}),
            dataType: 'json',
            contentType: 'application/json;charset=utf-8',
            url: '/api/' + apiMethod
        }).done(function (data) {
                if (handlers && typeof(handlers.success) == 'function') {
                    handlers.success(data);
                }
                return data;
            }
        ).fail(failed);
    }

    return {
        getAddresses: function (id, callback) {
            return load('getAddresses', {id: -1}, new Handlers(callback));
        },
        getGoods: function (id) {
            return load('getGoods', {id: -1});
        },
        saveGoodsOrder: function (data, callback) {
            return load('saveOrder', data, new Handlers(callback));
        },
        getNextGoodCode: function (code, callback) {
            return load('getNextGoodCode', {code: code}, new Handlers(callback));
        },
        getDescriptionKeys: function (callback) {
            return load('getDescriptionKeys', null, new Handlers(callback))
        },
        getPrices: function (callback) {
            return load('getPrices', null, new Handlers(callback));
        },
        getGoodsKeys: function (callback) {
            return load('getGoodsKeys', null, new Handlers(callback));
        },
        updateGood: function (id, data, callback) {
            return load('updateGood', {id: id, data: data}, new Handlers(callback));
        },
        getGood: function (id, callback) {
            return load('getGood', {id: id}, new Handlers(callback));
        },
        getGoodsAdminOrder: function (callback) {
            return load('getAdminOrder', null, new Handlers(callback));
        },
        getGoodImages: function (id, callback) {
            return load('getGoodImages', {id: id}, new Handlers(callback));
        },
        deleteGood: function (id, callback) {
            return load('deleteGood', {id: id}, new Handlers(callback));
        },
        uploadImagesForGood: function (id, data, callback) {
            return load('uploadImagesForGood', {id: id, data: data}, new Handlers(callback));
        },
        updatePrices: function (data, callback) {
            return load('updatePrices', {data: data}, new Handlers(callback));
        },
        readImagesFromCatalogToDb: function readImagesFromCatalogToDb() {
            return load('readImagesFromCatalogToDb');
        },
        /*****************************************Booklets*************************************/
        listBooklets: function (mapping, callback) {
            return load('listBooklets', {mapping: mapping}, new Handlers(callback));
        },
        getBooklet: function (id, mapping, callback) {
            return load('getBooklet', {id: id, mapping: mapping}, new Handlers(callback));
        },
        saveBooklet: function (data, callback) {
            return load('saveBooklet', {data: data}, new Handlers(callback));
        },
        deleteBooklet: function (id, callback) {
            return load('deleteBooklet', {id: id}, new Handlers(callback));
        },
        getBookletBackgrounds: function (callback) {
            return load('getBookletBackgrounds', null, new Handlers(callback));
        }
    }
});
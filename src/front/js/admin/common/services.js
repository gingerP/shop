define([
    'common/handlers'
], function (Handlers) {
    function load(apiMethod, params, handlers) {
        $.ajax({
            type: 'POST',
            data: JSON.stringify(params),
            dataType: 'json',
            contentType:  'application/json;charset=utf-8',
            url: '/api/' + apiMethod
        }).done(function (data) {
                if (typeof(handlers.success) == 'function') {
                    handlers.success(data);
                }
            }
        ).fail(function (error) {
            dhtmlx.alert({
                title: 'Alert',
                type: 'alert-error',
                text: '<span style="word-break: break-all">' + error.responseJSON.message + '</span>',
                callback: function callback() {
                    if (error.status === 401) {
                        document.location.reload(true);
                    }
                }
            });
        });
    }

    return {
        getAddresses: function (id, callback) {
            load('getAddresses', {id: -1}, new Handlers(callback));
        },
        getGoods: function (id, callback) {
            load('getGoods', {id: -1}, new Handlers(callback));
        },
        saveGoodsOrder: function (data, callback) {
            load('saveOrder', data, new Handlers(callback));
        },
        getNextGoodCode: function (code, callback) {
            load('getNextGoodCode', {code: code}, new Handlers(callback));
        },
        getDescriptionKeys: function (callback) {
            load('getDescriptionKeys', null, new Handlers(callback))
        },
        getPrices: function (callback) {
            load('getPrices', null, new Handlers(callback));
        },
        getGoodsKeys: function (callback) {
            load('getGoodsKeys', null, new Handlers(callback));
        },
        updateGood: function (id, data, callback) {
            load('updateGood', {id: id, data: data}, new Handlers(callback));
        },
        getGood: function (id, callback) {
            load('getGood', {id: id}, new Handlers(callback));
        },
        getGoodsAdminOrder: function (callback) {
            load('getAdminOrder', null, new Handlers(callback));
        },
        getGoodImages: function (id, callback) {
            load('getGoodImages', {id: id}, new Handlers(callback));
        },
        deleteGood: function (id, callback) {
            load('deleteGood', {id: id}, new Handlers(callback));
        },
        uploadImagesForGood: function (id, data, callback) {
            load('uploadImagesForGood', {id: id, data: data}, new Handlers(callback));
        },
        updatePrices: function (data, callback) {
            load('updatePrices', {data: data}, new Handlers(callback));
        },
        /*****************************************Booklets*************************************/
        listBooklets: function (mapping, callback) {
            load('listBooklets', {mapping: mapping}, new Handlers(callback));
        },
        getBooklet: function (id, mapping, callback) {
            load('getBooklet', {id: id, mapping: mapping}, new Handlers(callback));
        },
        saveBooklet: function (data, callback) {
            load('saveBooklet', {data: data}, new Handlers(callback));
        },
        deleteBooklet: function (id, callback) {
            load('deleteBooklet', {id: id}, new Handlers(callback));
        },
        getBookletBackgrounds: function (callback) {
            load('getBookletBackgrounds', null, new Handlers(callback));
        }
    }
});
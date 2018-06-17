define([
    'axios',
    'common/handlers',
    'common/authorization',
    'lodash'
], function (axios, Handlers, Authorization, _) {

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
        //api.saveError(message, typeof error.stack === 'string' ? error.stack : '');
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

    function load(method, apiPath, params, handlers) {
        return axios({
            method: method,
            data: params || {},
            dataType: 'json',
            headers: Authorization.headers({contentType: 'application/json;charset=utf-8'}),
            url: '/api' + apiPath
        })
            .then(function (data) {
                if (handlers && typeof(handlers.success) === 'function') {
                    handlers.success(data);
                }
                return data;
            })
            .catch(function (data) {
                if (data && data.response && data.response.status === 401) {
                    return Authorization
                        .refreshToken()
                        .then(function () {
                            return load(method, apiPath, params, handlers);
                        })
                        .catch(Authorization.logout);
                }
                throw data;
            })
            .catch(failed);
    }

    function getData(response) {
        return response && response.data;
    }

    function post(apiPath, params, handlers) {
        return load('POST', '/admin' + apiPath, params, handlers).then(getData);
    }

    function get(apiPath, params, handlers) {
        return load('GET', '/admin' + apiPath, params, handlers).then(getData);
    }

    function delete_(apiPath, params, handlers) {
        return load('DELETE', '/admin' + apiPath, params, handlers).then(getData);
    }

    var api = {
        saveError: function (message, stack) {
            return post('/errors', {message: message, stack: stack, pageUrl: window.location.href});
        },
        getAddresses: function () {
            return get('/addresses');
        },
        getGoods: function () {
            return get('/products');
        },
        saveGoodsOrder: function (data) {
            return post('/products/order', data);
        },
        getNextGoodCode: function (code) {
            return get('/products/next_code/' + code);
        },
        getDescriptionKeys: function () {
            return get('/settings', null);
        },
        getPrices: function () {
            return get('getPrices', null);
        },
        updateGood: function (data) {
            return post('/products', data);
        },
        getGood: function (id) {
            return get('/products/' + id);
        },
        getGoodsAdminOrder: function () {
            return get('/products/order');
        },
        getGoodImages: function (id) {
            return get('/products/' + id + '/images');
        },
        deleteGood: function (id) {
            return delete_('/products/' + id);
        },
        uploadImagesForGood: function (id, data) {
            return post('/products/' + id + '/images', data);
        },
        updatePrices: function (data) {
            return post('updatePrices', {data: data});
        },
        readImagesFromCatalogToDb: function readImagesFromCatalogToDb() {
            return get('readImagesFromCatalogToDb');
        },
        getAdminSettings: function getAdminSettings() {
            return get('/settings');
        },
        getCategories: function getCategories() {
            return get('/categories');
        },
        /*****************************************Booklets*************************************/
        listBooklets: function (mapping) {
            return get('/booklets?mapping=' + encodeURIComponent(JSON.stringify(mapping)));
        },
        getBooklet: function (id, mapping) {
            if (mapping) {
                return get('/booklets/' + id + '?mapping=' + encodeURIComponent(JSON.stringify(mapping)));
            }
            return get('/booklets/' + id);
        },
        saveBooklet: function (data) {
            return post('/booklets', data);
        },
        deleteBooklet: function (id) {
            return delete_('/booklets/' + id);
        },
        getBookletBackgrounds: function () {
            return get('/booklets/backgrounds/');
        }
    };
    return api;
});

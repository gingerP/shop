define([
    'jquery',
    'bazil',
    'lodash',
    'axios'
], function ($, Basil, _, axios) {
    'use strict';
    var basil = new Basil({
        // Namespace. Namespace your Basil stored data
        // default: 'b45i1'
        namespace: 'au',

        // storages. Specify all Basil supported storages and priority order
        // default: `['local', 'cookie', 'session', 'memory']`
        storages: ['local', 'session', 'cookie'],

        // storage. Specify the default storage to use
        // default: detect best available storage among the supported ones
        storage: 'local'
    });
    var TOKEN = 'token';
    var REFRESH_TOKEN = 'refreshToken';

    function headers(headersObj) {
        var preparedHeaders = headersObj || {};
        var authorization = basil.get(TOKEN);
        if (!_.isNil(authorization)) {
            preparedHeaders.Authorization = 'Bearer ' + authorization;
        }
        return preparedHeaders;
    }

    function isAuth() {
        var authorization = basil.get(TOKEN);
        return !_.isNil(authorization);
    }

    function storeToken(token) {
        basil.set(TOKEN, token);
    }

    function removeToken() {
        basil.remove(TOKEN);
    }

    function removeRefreshToken() {
        basil.remove(REFRESH_TOKEN);
    }

    function storeRefreshToken(token) {
        basil.set(REFRESH_TOKEN, token);
    }

    function logout() {
        removeToken();
        removeRefreshToken();
        window.location.reload(true);
    }

    function refreshToken() {
        return axios({
            method: 'POST',
            headers: {contentType: 'application/json;charset=utf-8'},
            url: '/api/admin/auth/refresh',
            dataType: 'json',
            data: {
                refreshToken: basil.get(REFRESH_TOKEN)
            }
        })
            .then(function (response) {
                storeToken(response.data.token);
            });
    }

    function authorize() {
        return new Promise(function (resolve, reject) {
            if (!isAuth()) {
                reject();
            } else {
                refreshToken()
                    .then(resolve)
                    .catch(logout);
            }
        });
    }

    function getSessionTtl() {
        var token = basil.get(REFRESH_TOKEN);
        if (token) {
            var parts = token.split('.');
            var jwtInfo = JSON.parse(atob(parts[1]));
            return jwtInfo.exp;
        }
    }

    /**
     * @typedef {{
     *      isAuth: function(),
     *      headers: function(headers),
     *      authorize: function(),
     *      storeToken: function(token)
     *      refreshToken: function()
     * }} Authorization
     */
    return {
        logout: logout,
        isAuth: isAuth,
        headers: headers,
        authorize: authorize,
        storeToken: storeToken,
        removeToken: removeToken,
        storeRefreshToken: storeRefreshToken,
        removeRefreshToken: removeRefreshToken,
        refreshToken: refreshToken,
        getSessionTtl: getSessionTtl
    };
});

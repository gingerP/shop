define([
    'angular',
    'q',
    'components/common.module'
], function (angular, q) {
    'use strict';

    angular.module('common.module').factory('commonFactory', commonFactory);

    function commonFactory($http) {
        function load(method, data) {
            return $http({
                method: 'POST',
                data: $.param(data),
                url: "/api/" + method,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }).then(function(response) {
                return response.data;
            });
        }

        function loadNews(page, offset) {
            return load('loadNews', {
                page: page,
                offset: offset
            });
        }

        return {
            loadNews: loadNews
        };
    }

});

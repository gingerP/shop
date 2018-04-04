(function (angular) {
    angular.module('common.module', []).factory('CommonFactory', ['$http', CommonFactory]);
    function CommonFactory($http) {
        function get(endpoint) {
            return $http({
                method: 'GET',
                url: '/api/' + endpoint,
                headers: {
                    'Content-Type': 'application/json'
                }
            }).then(function (response) {
                return response.data;
            });
        }
        function load(method, data) {
            return $http({
                method: 'POST',
                data: data,
                url: '/api/' + method,
                headers: {
                    'Content-Type': 'application/json'
                }
            }).then(function (response) {
                return response.data;
            });
        }

        function loadNews(page, offset) {
            return load('loadNews', {
                page: page,
                offset: offset
            });
        }

        function loadContacts() {
            return get('contacts');
        }

        return {
            loadNews: loadNews,
            loadContacts: loadContacts
        };
    }
})(angular);

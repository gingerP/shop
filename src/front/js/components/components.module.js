(function (angular) {
    'use strict';

    angular.module('app', [
        'common.module',
        'news.module',
        'news.gallery.module',
        'contacts.module'
    ]);
    angular.element(document).ready(function () {
        angular.bootstrap(document, ['app']);
    });
})(angular);

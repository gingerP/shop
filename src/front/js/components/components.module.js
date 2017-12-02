(function () {
    'use strict';

    angular.module('app', [
        'common.module',
        'news.module',
        'news.gallery.module'
    ]);
    angular.element(document).ready(function () {
        angular.bootstrap(document, ['app']);
    });
})();

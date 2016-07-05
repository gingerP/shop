define([
    'angular',
    'components/news/components/news.component',
    'components/news.gallery/components/news.gallery.component',
    'components/common.module'
], function(angular) {
    'use strict';

    angular.module('app', [
        'common.module',
        'news.module',
        'news.gallery.module'
    ]);
    angular.element(document).ready(function() {
        angular.bootstrap(document, ['app']);
    });
});
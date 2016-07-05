define([
    'angular',
    'components/news/news.module',
    'components/news/controllers/news.controller'
], function(angular) {
    'use strict';

    angular.module('news.module').component('newsComponent', {
        controller: 'newsController',
        controllerAs: 'newsCtrl',
        templateUrl: 'src/front/js/components/news/partials/news.template.html'
    });
});

define([
    'angular',
    'components/news/news.module',
    'components/common.factory'
], function(angular) {
    'use strict';

    angular.module('news.module').controller('newsController', NewsController);

    function NewsController($scope, $sce, commonFactory) {
        var vm = this,
            page = -1,
            countPerPage = 2;

        vm.news = [];

        vm.$onInit = function() {
            vm.loadMoreNews();
        };

        vm.loadMoreNews = function loadMoreNews() {
            page++;
            commonFactory.loadNews(page, countPerPage).then(function(data) {
                vm.news = vm.news.concat(data);
            });
        };
        vm.renderHtml = function(text) {
            return $sce.trustAsHtml(text);
        }

    }
});

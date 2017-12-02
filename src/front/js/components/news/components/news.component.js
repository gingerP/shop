(function () {
    angular.module('news.module', []);
    angular.module('news.module').controller('newsController', ['$sce', 'commonFactory', NewsController]);

    function NewsController($sce, commonFactory) {
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
    angular.module('news.module').component('newsComponent', {
        controller: 'newsController',
        controllerAs: 'newsCtrl',
        templateUrl: 'src/front/js/components/news/partials/news.template.html'
    });
})();

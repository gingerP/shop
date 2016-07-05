define([
    'angular',
    'components/news.gallery/news.gallery.module',
    'components/news.gallery/controllers/news.gallery.controller'
], function(angular) {
    'use strict';

    angular.module('news.gallery.module').directive('newsGalleryComponent', newsGalleryComponent);

    function newsGalleryComponent() {
        var NEWS_GALLERY_ITEM = 'news-gallery-item';
        var NEWS_GALLERY_CONTAINER = 'news-gallery-container';
        var NEWS_GALLERY_LIST_CONTAINER = 'news-gallery-list-container';
        var width = -1;

        function linkFunc(scope, element) {

            scope.$on('load-news', function(event, news) {
                var listContainer = element.find('.' + NEWS_GALLERY_LIST_CONTAINER);
                var width = getWidth(element);
                listContainer.css('width', news.length * width + 'px');
            });

            scope.$watch('newsGalleryCtrl.news.length', function() {
                var width = getWidth(element);
                var news = element.find('.' + NEWS_GALLERY_ITEM)
                    .css({
                        'width': width + 'px'/*,
                        'margin': MARGIN_HOR + 'px'*/
                    })
            });

            scope.$on('apply-position', function(event, data) {
                var width = getWidth(element);
                element.find('.' + NEWS_GALLERY_LIST_CONTAINER).css('left', '-' + (data.leftStep * width) + 'px')
            });
        }

        function getWidth(element) {
            if (width === -1) {
                width = element.find('.' + NEWS_GALLERY_CONTAINER).width();
            }
            return width;
        }

        return {
            controller: 'newsGalleryController',
            controllerAs: 'newsGalleryCtrl',
            templateUrl: 'src/front/js/components/news.gallery/partials/news.gallery.template.html',
            link: linkFunc
        }
    }
});
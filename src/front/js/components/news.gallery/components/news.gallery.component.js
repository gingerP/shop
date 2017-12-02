(function () {
    'use strict';

    angular.module('news.gallery.module', []);
    angular.module('news.gallery.module').controller('newsGalleryController', ['$sce', '$scope', 'commonFactory', NewsGalleryController]);

    function NewsGalleryController($sce, $scope, commonFactory) {
        var vm = this;

        vm.news = [];
        vm.order = [];

        vm.$onInit = function () {
            commonFactory.loadNews(0, 100).then(function (data) {
                vm.news = data || [];
                if (vm.news.length) {
                    vm.order = [];
                    for (var index = 0; index < vm.news.length; index++) {
                        vm.order.push(vm.news[index].id);
                    }
                    vm.select(vm.news[0]);
                    $scope.$broadcast('load-news', vm.news)
                }
            });
        };

        vm.select = function select(item) {
            var oldSelected;
            var oldSelectedIndex;
            var itemIndex;
            var includedIndexes;
            for (var index = 0; index < vm.news.length; index++) {
                var newsItem = vm.news[index];
                if (newsItem.isSelected === true) {
                    oldSelected = newsItem;
                    oldSelectedIndex = index;
                    newsItem.isSelected = false;
                }
                if (newsItem.id === item.id) {
                    itemIndex = index;
                }
            }
            includedIndexes = getIncludedIndexes(itemIndex, oldSelected ? oldSelectedIndex : undefined);
            hideEvery(includedIndexes);
            item.isSelected = true;
            $scope.$broadcast('apply-position', {leftStep: vm.news.length - includedIndexes.length - 1});
        };


        vm.select = function select(item) {
            var order = [];
            for (var index = 0; index < vm.order.length; index++) {
                if (vm.order[index].id === item.id) {
                    var newIndex = index;
                }
                if (vm.order[index].isSelected === true) {
                    var oldIndex = index;
                }
            }

            var newItem;
            var destIndex;
            if (oldIndex > newIndex && oldIndex - newIndex !== 1) {
                newItem = vm.order.splice(newIndex, 1)[0];
                //oldIndex became smaller by 1
                vm.order.splice(oldIndex - 1, 0, newItem);
            } else if (oldIndex < newIndex && newIndex - oldIndex !== 1) {
                newItem = vm.order.splice(newIndex, 1)[0];
                //oldIndex didn't changed
                vm.order.splice(oldIndex + 1, 0, newItem);
            }
            for (var index = 0; index < vm.news.length; index++) {
                var itemDyn = vm.news[index];
                itemDyn.isSelected = itemDyn.id === item.id;
                if (itemDyn.id === item.id) {
                    destIndex = index;
                }
            }
            $scope.$broadcast('apply-position', {
                leftStep: destIndex,
                order: vm.order
            });
        };

        vm.getSecureContent = function getSecureContent(item) {
            return $sce.trustAsHtml(item.content);
        };

        vm.getSecureUrlContent = function getSecureContent(item) {
            return $sce.trustAsHtml(item.content);
        };

        function getIncludedIndexes(newIndex, oldIndex) {
            var result = [];
            var ind;
            var search = 0;
            if (!angular.isDefined(oldIndex)) {
                vm.news.forEach(function (item, index) {
                    if (index !== newIndex) {
                        result.push(index);
                    }
                });
            } else {
                for (ind = vm.news.length - 1; ind >= 0; ind--) {
                    if (search < 2) {
                        search += ind === newIndex || ind === oldIndex ? 1 : 0;
                        if (ind !== newIndex && ind !== oldIndex) {
                            result.push(ind);
                        }
                    }
                }
            }
            return result;
        }

        function hideEvery(include) {
            vm.news.forEach(function (newsItem) {
                newsItem.isVisible = include.indexOf(newsItem.id) < 0;
            });
        }

        function applyPositions(newId, oldId) {
            if (!angular.isDefined(oldId)) {
                return 0;
            }
            return newId < oldId ? 0 : 1;
        }

    }

    angular.module('news.gallery.module').directive('newsGalleryComponent', newsGalleryComponent);

    function newsGalleryComponent() {
        var NEWS_GALLERY_ITEM = 'news-gallery-item';
        var NEWS_GALLERY_CONTAINER = 'news-gallery-container';
        var NEWS_GALLERY_LIST_CONTAINER = 'news-gallery-list-container';
        var width = -1;

        function linkFunc(scope, element) {

            scope.$on('load-news', function (event, news) {
                var listContainer = element.find('.' + NEWS_GALLERY_LIST_CONTAINER);
                var width = getWidth(element);
                listContainer.css('width', news.length * width + 'px');
            });

            scope.$watch('newsGalleryCtrl.news.length', function () {
                var width = getWidth(element);
                var news = element.find('.' + NEWS_GALLERY_ITEM)
                    .css({
                        'width': width + 'px'/*,
                         'margin': MARGIN_HOR + 'px'*/
                    })
            });

            scope.$on('apply-position', function (event, data) {
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

})();

define([
    'angular',
    'lodash',
    'components/news.gallery/news.gallery.module',
    'components/common.factory'
], function (angular, _) {
    'use strict';

    angular.module('news.gallery.module').controller('newsGalleryController', NewsGalleryController);


    function NewsGalleryController($sce, $scope, commonFactory) {
        var vm = this;

        vm.news = [];
        vm.order = [];

        vm.$onInit = function () {
            commonFactory.loadNews(0, 100).then(function (data) {
                vm.news = data || [];
                if (vm.news.length) {
                    vm.order = _.map(vm.news, {id: 'id'});
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
            var visibleCount =
            _.forEach(vm.news, function (newsItem, index) {
                if (newsItem.isSelected === true) {
                    oldSelected = newsItem;
                    oldSelectedIndex = index;
                    newsItem.isSelected = false;
                }
                if (newsItem.id === item.id) {
                    itemIndex = index;
                }
            });
            includedIndexes = getIncludedIndexes(itemIndex, oldSelected ? oldSelectedIndex : undefined);
            hideEvery(includedIndexes);
            item.isSelected = true;
            $scope.$broadcast('apply-position', {leftStep: vm.news.length - includedIndexes.length - 1});
        };

        vm.select = function select(item) {
            var order = [];
            var newIndex = _.findIndex(vm.order, {id: item.id});
            var oldIndex = _.findIndex(vm.order, {isSelected: true});
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
            _.forEach(vm.news, function(itemDyn, index) {
                itemDyn.isSelected = itemDyn.id === item.id;
                if (itemDyn.id === item.id) {
                    destIndex = index;
                }
            });
            $scope.$broadcast('apply-position', {
                leftStep: destIndex,
                order: vm.order
            });
        };

        vm.getSecureContent = function getSecureContent(item) {
            return $sce.trustAsHtml(item.content);
        };

        function getIncludedIndexes(newIndex, oldIndex) {
            var result = [];
            var ind;
            var search = 0;
            if (!angular.isDefined(oldIndex)) {
                vm.news.forEach(function(item, index) {
                    if (index !== newIndex) {
                        result.push(index);
                    }
                });
            } else {
                for(ind = vm.news.length - 1; ind >= 0; ind--) {
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
});
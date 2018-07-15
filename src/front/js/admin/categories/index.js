require(
    [
        'common/authorization',
        'common/authorization-view',
        'common/dialog',
        'common/service-entities',
        'common/services',
        'common/components',
        'dropbox/dropbox',
        'categories/categories-list',
        'categories/categories-details',
        'categories/categories-toolbar'
    ],
    function (/**@type Authorization*/
              Authorization,
              AuthorizationView,
              Dialog, ServiceEntities, Services, Components, AuDropboxDir,
              CategoriesList, CategoriesDetails, CategoriesToolbar) {
        'use strict';

        function initLayout() {
            var layout = new dhtmlXLayoutObject({
                skin: Components.skin,
                parent: document.body,
                pattern: '2U',
                cells: [
                    {id: 'a', text: 'Категории', width: 450},
                    {id: 'b', text: 'Детали категории'}
                ]
            });
            layout.setOffsets({
                top: 0,
                right: 0,
                bottom: 0,
                left: 0
            });
            layout.cells('a').hideHeader();
            layout.detachHeader();
            return layout;
        }

        function initServiceEntities() {
            var entity = {
                id: null,
                code: null,
                image: null,
                key_item: null,
                value: null
            };
            return new ServiceEntities().init(entity, 'id', 1);
        }

        function init() {
            AuUtils.dhtmlxDOMPreInit(document.documentElement, document.body);
            var layout = initLayout();
            var cloud = new AuDropboxDir();
            Components.createMenu(layout);
            var serviceEntities = initServiceEntities();
            var categoriesList = CategoriesList(layout);
            var categoriesDetails = CategoriesDetails(layout, categoriesList, cloud);
            var toolbar = CategoriesToolbar(layout, categoriesList, categoriesDetails, cloud, serviceEntities);
            categoriesList.reloadGrid();
        }

        Authorization.authorize()
            .then(init)
            .catch(function (error) {
                console.error(error);
                AuthorizationView.openLoginForm().then(init);
            });
    }
);

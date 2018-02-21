require([
    'common/service-entities',
    'common/services',
    'common/components',
    'settings/photo-tab'
], function (ServiceEntities, Services, Components, PhotoTab) {

    function initTabbar(layout) {
        return layout.cells('b').attachTabbar();
    }

    function createLayout() {
        var layout = new dhtmlXLayoutObject({
            skin: Components.skin,
            parent: document.body,
            pattern: '1C'
        });
        layout.cells('a').hideHeader();
        layout.detachHeader();
        return layout;
    }

    function createTabbar(layout) {
        return layout.cells('a').attachTabbar({
            align: 'left',
            mode: 'top',
            tabs: [
                {id: 'a1', text: 'Фото', active: true}
            ]
        });
    }

    (function init() {
        U.dhtmlxDOMPreInit(document.documentElement, document.body);
        app.layout = createLayout();
        app.tabbar = createTabbar(app.layout);
        app.menu = Components.createMenu(app.layout);
        PhotoTab.init(app, app.tabbar.tabs('a1'));
    })();
});
require([
    'common/authorization',
    'common/authorization-view',
    'common/service-entities',
    'common/services',
    'common/components',
    'settings/common-tab'
], function (/**@type Authorization*/
             Authorization,
             AuthorizationView,
             ServiceEntities, Services, Components, CommonTab) {

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
        layout.setOffsets({
            top: 0,
            right: 0,
            bottom: 0,
            left: 0
        });
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

    function init() {
        AuUtils.dhtmlxDOMPreInit(document.documentElement, document.body);
        app.layout = createLayout();
        app.tabbar = createTabbar(app.layout);
        app.menu = Components.createMenu(app.layout);
        CommonTab.init(app, app.tabbar.tabs('a1'));
    }

    Authorization.authorize()
        .then(init)
        .catch(function () {
            AuthorizationView.openLoginForm().then(init);
        });
});
require([
    'common/authorization',
    'common/authorization-view',
    'dropbox/dropbox',
    'common/components'
], function (/**@type Authorization*/
             Authorization,
             AuthorizationView,
             Dropbox, Components) {
    'use strict';

    function init() {
        AuUtils.dhtmlxDOMPreInit(document.documentElement, document.body);
        var app = {};
        /**@type AuDropboxDir*/
        var dropbox = new Dropbox();
        dropbox.openAsPage(document.body);
        dropbox.hideAddToProductButton();
        app.layout = dropbox.getLayout();
        app.menu = Components.createMenu(app.layout);
    }

    Authorization.authorize()
        .then(init)
        .catch(function () {
            AuthorizationView.openLoginForm().then(init);
        });
});

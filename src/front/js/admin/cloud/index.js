require([
    'dropbox/dropbox',
    'common/components',
    'settings/photo-tab'
], function (Dropbox, Components) {
    'use strict';

    AuUtils.dhtmlxDOMPreInit(document.documentElement, document.body);
    var app = {};
    /**@type AuDropboxDir*/
    var dropbox = new Dropbox();
    dropbox.openAsPage(document.body);
    dropbox.hideAddToProductButton();
    app.layout = dropbox.getLayout();
    app.menu = Components.createMenu(app.layout);
});

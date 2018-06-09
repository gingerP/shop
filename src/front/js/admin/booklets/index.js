require([
    'common/authorization',
    'common/authorization-view',
    'common/services',
    'common/components',
    'common/dialog',
    'booklets/booklet-component',
    'booklets/booklet-controller',
    'booklets/booklet-background-component',
    'booklets/booklet-full-page-preview',
    'booklets/handlebars-helpers'
], function (/**@type Authorization*/
             Authorization,
             AuthorizationView,
             Services, Components, Dialog, BookletComponent, BookletController, BookletBackground, FullPagePreview) {

    function initLayout() {
        var layout = new dhtmlXLayoutObject({
            skin: Components.skin,
            parent: document.body,
            pattern: '2U',
            cells: [
                {id: 'a', text: 'Буклеты', width: 400},
                {id: 'b', text: 'Редактирование'}
            ]
        });
        layout.setOffsets({
            top: 0,
            right: 0,
            bottom: 0,
            left: 0
        });
        layout.detachHeader();
        return layout;
    }

    function initMenu(app, layout) {
        return Components.createMenu(layout);
    }

    function initPage() {
        AuUtils.dhtmlxDOMPreInit(document.documentElement, document.body);
        var app = {
            unSelectionStateDetails: {
                items: {
                    add: {enableState: false},
                    setBackground: {enableState: false},
                    preview: {enableState: false},
                    templates: {enableState: false}
                }
            },
            selectionState: {
                items: {
                    save: {enableState: true},
                    delete: {enableState: true}
                }
            },
            selectionStateDetails: {
                items: {
                    add: {enableState: true},
                    setBackground: {enableState: true},
                    preview: {enableState: true},
                    templates: {enableState: true}
                }
            },
            unSelectionState: {hasSelection: false}
        };
        app.layout = initLayout();
        app.menu = initMenu(app, app.layout);
        app.booklet = initBookletPreview(app, app.layout);
        app.bookletsList = initBookletsList(app, app.layout);
        app.bookletsToolbar = initBookletsListToolbar(app, app.layout, app.bookletsList);
        app.bookletDetailsToolbar = initBookletEditorToolbar(app, app.layout.cells('b'));
        app.bookletsList.reload();
    }

    function initBookletsList(app, layout) {
        var grid = layout.cells('a').attachGrid();
        grid.setImagePath('/images/icons');
        grid.setHeader(['ID', 'Название']);
        grid.setInitWidths('60, 320');
        grid.setColAlign('left,left');
        grid.setColTypes('ro,ro');
        grid.setColSorting('int,str');
        grid.init();

        grid.attachEvent('onSelectStateChanged', function (id) {
            var gridState = {
                hasSelection: true
            };
            app.bookletsToolbar.onStateChange(gridState);
            app.bookletDetailsToolbar.onStateChange(app.selectionStateDetails);
            if (!app.booklet.isEmpty() && app.booklet.isNew()) {
                return;
            }
            app.booklet.controller.load(id);
        });


        grid.reload = function () {
            Services.listBooklets({id: 'booklet_id', name: 'name'})
                .then(function (booklets) {
                    Components.reloadGrid(grid, booklets, ['id', 'name']);
                    app.bookletsToolbar.onStateChange({hasSelection: false});
                    app.bookletDetailsToolbar.onStateChange(app.unSelectionStateDetails);
                })
                .catch(Dialog.error);
        };

        grid.addNewBooklet = function () {
            var newBooklet = app.booklet.createNewBooklet();
            var rowArray = Components.prepareEntityToGrid(newBooklet, ['id', 'name']);
            this.addRow(newBooklet.id, rowArray, 0);
            this.selectRowById(newBooklet.id);
        };

        return grid;
    }

    function initBookletsListToolbar(app, layout) {
        var handlers = {
            reload: function () {
                app.bookletsList.reload();
                app.booklet.clear();
            },
            add: function () {
                if (app.booklet.canCreateNew()) {
                    app.bookletsList.addNewBooklet();
                    app.bookletsToolbar.onStateChange(app.selectionState);
                    app.bookletDetailsToolbar.onStateChange(app.selectionStateDetails);
                }
            },
            save: function () {
                var oldId;
                var newId;
                app.layout.progressOn();
                return app.booklet.save()
                    .then(function (saveResult) {
                        oldId = saveResult[0];
                        newId = saveResult[1];
                        app.bookletsToolbar.onStateChange(app.selectionState);
                        app.bookletDetailsToolbar.onStateChange(app.selectionStateDetails);
                        return Services.getBooklet(newId, {id: 'booklet_id', name: 'name'});
                    })
                    .then(function (data) {
                        Components.updateGridRow(app.bookletsList, oldId, data, ['id', 'name'], newId);
                        app.bookletsList.clearSelection();
                        app.bookletsList.selectRowById(newId);
                        app.layout.progressOff();
                        Dialog.success('Буклет успешно сохранен.');
                    })
                    .catch(function (error) {
                        app.layout.progressOff();
                        Dialog.error(error);
                    });
            },
            delete: function () {
                return Dialog.confirm('Вы уверены, что хотите удалить буклет?')
                    .then(function () {
                        app.layout.progressOn();
                        return app.booklet.delete()
                            .then(function () {
                                app.bookletsList.deleteSelectedRows();
                                app.bookletsToolbar.onStateChange(app.unSelectionState);
                                app.bookletDetailsToolbar.onStateChange(app.unSelectionStateDetails);
                                app.layout.progressOff();
                                Dialog.success('Буклет успешно удален.');
                            })
                            .catch(function (error) {
                                app.layout.progressOff();
                                Dialog.error(error);
                            });
                    });
            }
        };
        var toolbar = Components.createToolbar(layout, handlers);
        toolbar.onStateChange({hasSelection: false});
        return toolbar;
    }

    function initBookletEditorToolbar(app, layout) {
        var handlers = {
            add: function () {
                app.booklet.addNewItem();
            },
            preview: function () {
                var entity = app.booklet.controller.getEntity();
                if (!entity._isNew) {
                    window.open(window.location + '?id=' + entity.id);
                }
            },
            setBackground: function () {
                BookletBackground.show();
            }
        };

        BookletBackground.addSelectCallback(function (imageObject) {
            if (imageObject) {
                app.booklet.updateBackground(imageObject.image);
            }
        });
        var toolbar = Components.createToolbar(layout, handlers, [
            'add',
            'preview',
            {
                type: 'buttonSelect', id: 'templates', text: 'Разметка', openAll: true,
                options: [
                    {type: 'button', id: '2-col', text: '2 колонки'},
                    {type: 'button', id: '3-col', text: '3 колонки'}
                ]
            },
            {
                type: 'button', id: 'setBackground', text: 'Установить фон', img: 'background.png',
                img_disabled: 'background_dis.png'
            }
        ]);
        toolbar.onStateChange(app.unSelectionStateDetails);
        toolbar.attachEvent('onClick', function (id) {
            switch (id) {
                case '2-col':
                    app.booklet.setTemplate('2c3c');
                    break;
                case '3-col':
                    app.booklet.setTemplate('3c2c');
                    break;
            }
        });

        return toolbar;
    }

    function initBookletPreview(app, layout) {
        layout.cells('b').attachHTMLString('<div id=\'booklets\'></div>');
        var booklet = new BookletComponent(document.getElementById('booklets'), new BookletController().init()).init();
        return booklet;
    }

    if (AuUtils.hasContent(URL_PARAMS.id)) {
        FullPagePreview.render(URL_PARAMS.id);
    } else {
        Authorization.authorize()
            .then(initPage)
            .catch(function () {
                AuthorizationView.openLoginForm().then(initPage);
            });
    }
});

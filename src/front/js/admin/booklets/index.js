require([
    'common/services',
    'common/components',
    'booklets/booklet-component',
    'booklets/booklet-controller',
    'booklets/booklet-item-component',
    'booklets/booklet-item-controller',
    'booklets/handlebars-helpers'
], function (Services, Components, BookletComponent, BookletController,
             BookletItem, BookletItemController, HandlebarsHelpers) {



    function initFullPreviewPage(id) {
        var sizeRatio = 4;
        var initialViewportSize = {
            width: 1000,
            height: 720
        };

        function updateItemSize(dom, size) {
            dom.style.width = (size.width * sizeRatio || 0) + 'px';
            dom.style.height = (size.height * sizeRatio || 0) + 'px';
        }

        function updateItemPosition(dom, position) {
            TweenLite.to(dom, 0, {
                x: position.x * sizeRatio,
                y: position.y * sizeRatio
            });
        }

        Services.getBooklet(id)
            .then(function (booklet) {
                document.body.style.overflow = 'initial';
                var container = document.createElement('DIV');
                container.id = 'fullpreview';
                document.body.appendChild(container);
                var dom = U.compilePrefillHandlebar('fullpreviewContainer', booklet);
                container.appendChild(dom);
                var viewport = document.getElementsByClassName('viewport')[0];
                viewport.style.width = (initialViewportSize.width * sizeRatio) + 'px';
                viewport.style.height = (initialViewportSize.height * sizeRatio) + 'px';
                if (booklet.listItems && booklet.listItems.length) {
                    booklet.listItems.forEach(function (item) {
                        var itemDOM = document.getElementById(item.id);
                        if (itemDOM) {
                            updateItemPosition(itemDOM, item.position);
                            updateItemSize(itemDOM, item.size);
                        } else {
                            console.warn('DOM object for id=' + item.id + ' was not rendered!')
                        }
                    })
                }
            })
    }

    function initLayout() {
        var layout = new dhtmlXLayoutObject({
            skin: Components.skin,
            parent: document.body,
            pattern: "2U",
            cells: [
                {id: "a", text: "Буклеты", width: 400},
                {id: "b", text: "Предпросмотр"}
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
        U.dhtmlxDOMPreInit(document.documentElement, document.body);
        unSelectionStateDetails = {
            items: {
                add: {
                    enableState: false
                },
                loadBackground: {
                    enableState: false
                },
                preview: {
                    enableState: false
                }
            }
        };
        selectionState = {
            items: {
                save: {
                    enableState: true
                },
                delete: {
                    enableState: true
                }
            }
        };
        selectionStateDetails = {
            items: {
                add: {
                    enableState: true
                },
                loadBackground: {
                    enableState: true
                }
            }
        };

        unSelectionState = {
            hasSelection: false
        };
        var app = {};
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
        grid.setColAlign("left,left");
        grid.setColTypes("ro,ro");
        grid.setColSorting("int,str");
        grid.init();

        grid.attachEvent("onSelectStateChanged", function (id) {
            var entity = this.getUserData(id, 'entity');
            var gridState = {
                hasSelection: true
            };
            app.bookletsToolbar.onStateChange(gridState);
            app.bookletDetailsToolbar.onStateChange(gridState);
            if (!app.booklet.isEmpty() && app.booklet.isNew()) {
                return;
            }
            app.booklet.controller.load(id);
            /*serviceWrapper.getBooklet(id, {}, function(data) {
             app.booklet.clear();
             if (U.hasContent(data)) {
             app.booklet.populate(data);
             }
             })*/
        });


        grid.reload = function () {
            Services.listBooklets({id: 'booklet_id', name: 'name'})
                .then(function (booklets) {
                    Components.reloadGrid(grid, booklets, ['id', 'name']);
                    app.bookletsToolbar.onStateChange({hasSelection: false});
                    app.bookletDetailsToolbar.onStateChange(unSelectionStateDetails);
                })
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
                    app.bookletsToolbar.onStateChange(selectionState);
                    app.bookletDetailsToolbar.onStateChange(selectionStateDetails);
                }
            },
            save: function () {
                app.booklet.save(function (oldId, newId) {
                    app.bookletsToolbar.onStateChange(selectionState);
                    app.bookletDetailsToolbar.onStateChange(selectionStateDetails);
                    Services.getBooklet(newId, {id: 'booklet_id', name: 'name'})
                        .then(function (data) {
                            Components.updateGridRow(app.bookletsList, oldId, data, ['id', 'name'], newId);
                            app.bookletsList.clearSelection();
                            app.bookletsList.selectRowById(newId);
                        })
                });
            },
            delete: function () {
                app.booklet.delete(function () {
                    app.bookletsList.deleteSelectedRows();
                    app.bookletsToolbar.onStateChange(unSelectionState);
                    app.bookletDetailsToolbar.onStateChange(unSelectionStateDetails);
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
            loadBackground: function () {
                bookletBackgroundWindow.show();
            },
            preview: function () {
                var entity = app.booklet.controller.getEntity();
                if (!entity._isNew) {
                    window.open(window.location + '?id=' + entity.id);
                }
            }
        };

        bookletBackgroundWindow.addSelectCallback(function (imageObject) {
            if (imageObject) {
                app.booklet.updateBackground(imageObject.image);
            }
        });
        var toolbar = Components.createToolbar(layout, handlers, ['add', 'loadBackground', 'preview']);
        toolbar.onStateChange(unSelectionStateDetails);
        initTemplatesList(toolbar);
        return toolbar;
    }

    function initTemplatesList(toolbar) {
        var cfg = [
            get2ColumnConfig(),
            get3ColumnConfig()
        ];
        var index = 0;
        var item;
        toolbar.addButtonSelect('templates', null, "Шаблоны", [], null, null, true);
        while (index <= cfg.length - 1) {
            item = cfg[index];
            toolbar.addListOption(
                'templates',
                item.id,
                index,
                item.type,
                item.text
            );
            toolbar.attachEvent('onClick', item.handler);
            index++;
        }
    }

    function get2ColumnConfig() {
        return {
            id: '2-col',
            type: 'button',
            text: '2 колонки',
            handler: function handler2Column(id) {
                if (id === '2-col') {
                    app.booklet.setTemplate('2c3c');
                }
            }
        }
    }

    function get3ColumnConfig() {
        return {
            id: '3-col',
            type: 'button',
            text: '3 колонки',
            handler: function handler3Column(id) {
                if (id === '3-col') {
                    app.booklet.setTemplate('3c2c');
                }
            }
        }
    }

    function initBookletPreview(app, layout) {
        layout.cells('b').attachHTMLString("<div id='booklets'></div>");
        var booklet = new BookletComponent(document.getElementById('booklets'), new BookletController().init()).init();
        //booklet.enable();
        return booklet;
    }

    function formatBookletImage(src) {
        if (src && src.length > 0 && src.indexOf('data:image') != 0) {
            src = "/" + app.bookletImageRoot + "/" + src;
        }
        return src;
    }

    function evaluateSizeMultiplayer(size) {
        var res = 1;
        res *= Math.ceil(size.width / 135);
        res *= Math.ceil(size.height / 225);
        return res;
    }

    bookletBackgroundWindow = (function () {
        var selectCallbacks = [];
        var win = null;
        var form = null;
        var dataView = null;
        var bookletBackgroundWindowInstance = null;
        var selected = null;

        function unselectAll() {
            selected = null;
            if (dataView) {
                dataView.unselectAll();
            }
        }

        function initForm(window) {
            var formConfig = [
                {type: 'container', name: 'images', inputWidth: 730, inputHeight: 410},
                {
                    type: 'block', blockOffset: 7, width: 730, list: [
                    {type: 'button', name: 'cancel', value: 'Закрыть'},
                    {type: 'newcolumn'},
                    {type: 'button', name: 'ok', value: 'Выбрать', offsetLeft: 520}
                ]
                }
            ];
            var form = window.attachForm(formConfig);
            form.attachEvent('onButtonClick', function (name) {
                if (name == 'ok') {
                    var selectedId = dataView.getSelected();
                    if (U.hasContent(selectedId)) {
                        selected = dataView.get(selectedId);
                    }

                }
                if (name == 'cancel') {
                    unselectAll();
                }
                bookletBackgroundWindowInstance.hide();
            });
            return form;
        }

        function initDataView(container) {
            var view = new dhtmlXDataView({
                container: container,
                drag: false,
                select: true,
                template: "<img class='booklet_select_preview' src='#image#'/>"
            });
            Services.getBookletBackgrounds()
                .then(function (backgrounds) {
                    if (backgrounds && backgrounds.length) {
                        backgrounds.forEach(function (image) {
                            view.add({id: U.getRandomString(), image: "/" + image});
                        })
                    }
                    //console.info(backgrounds);
                });
            return view;
        }

        function closeCallback() {
            return function () {
                selectCallbacks.forEach(function (_callback) {
                    _callback(selected);
                })
            }
        }

        return {
            show: function () {
                bookletBackgroundWindowInstance = this;
                if (!win) {
                    win = Components.initDhtmlxWindow({
                        width: 740,
                        height: 500
                    }, closeCallback());
                    form = initForm(win);
                    dataView = initDataView(form.getContainer('images'));
                }
                win.show();
            },
            hide: function () {
                bookletBackgroundWindowInstance = this;
                win.close();
            },
            addSelectCallback: function (callback) {
                bookletBackgroundWindowInstance = this;
                if (typeof callback == 'function') {
                    selectCallbacks.push(callback);
                }
                unselectAll();
            }
        }
    })();

    //initHandlebarsExtensions();
    if (U.hasContent(URL_PARAMS.id)) {
        initFullPreviewPage(URL_PARAMS.id);
    } else {
        initPage();
    }
});

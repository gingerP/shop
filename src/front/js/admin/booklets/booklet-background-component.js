define([
    'common/components',
    'common/services',
    'common/dialog'
], function (Components, Services, Dialog) {
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

    function initForm(dhxWin) {
        var formConfig = [
            {type: 'container', name: 'images', inputWidth: 730, inputHeight: 410},
            {
                type: 'block', blockOffset: 7, width: 730,
                list: [
                    {type: 'button', name: 'cancel', value: 'Закрыть'},
                    {type: 'newcolumn'},
                    {type: 'button', name: 'ok', value: 'Выбрать', offsetLeft: 520}
                ]
            }
        ];
        var form = dhxWin.attachForm(formConfig);
        form.attachEvent('onButtonClick', function (name) {
            if (name === 'ok') {
                var selectedId = dataView.getSelected();
                if (U.hasContent(selectedId)) {
                    selected = dataView.get(selectedId);
                }

            }
            if (name === 'cancel') {
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
            type: {
                template: "<img class='booklet_select_preview' src='#image#'/>",
                width: 210,
                height: 115
            }
        });
        Services.getBookletBackgrounds()
            .then(function (backgrounds) {
                if (backgrounds && backgrounds.length) {
                    backgrounds.forEach(function (image) {
                        view.add({id: U.getRandomString(), image: "/" + image});
                    });
                }
                //console.info(backgrounds);
            })
            .catch(Dialog.error);
        return view;
    }

    function closeCallback() {
        return function () {
            selectCallbacks.forEach(function (_callback) {
                _callback(selected);
            });
        };
    }

    return {
        show: function () {
            bookletBackgroundWindowInstance = this;
            if (!win) {
                win = Components.initDhtmlxWindow({
                    width: 740,
                    height: 520,
                    caption: 'Выбрать фон'
                }, closeCallback());
                win.cell.className += ' booklet-background-component';
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
            if (typeof callback === 'function') {
                selectCallbacks.push(callback);
            }
            unselectAll();
        }
    };
});

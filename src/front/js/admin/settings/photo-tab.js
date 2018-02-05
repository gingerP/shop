define([
    'common/services'
], function (Services) {

    var form;
    var layout;

    function reloadImages() {
        layout.progressOn();
        return Services.readImagesFromCatalogToDb()
            .done(function(response) {
                layout.progressOff();
                dhtmlx.alert('Обработано товаров - ' + response.products + '<br>Обработано изображений - ' + response.images + '.');
            })
            .fail(function() {
                layout.progressOff();
            });
    }

    function init(app, tab) {
        layout = app.layout;
        form = tab.attachForm([
            {type: 'button', offsetLeft: 20, offsetTop: 20, name: 'read-images-to-db', value: 'Пересчитать изображения из каталога в базу данных'}
        ]);
        form.attachEvent('onButtonClick', function (name) {
            if (name === 'read-images-to-db') {
               reloadImages();
            }
        });
    }

    return {
        init: init
    };
});

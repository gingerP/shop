define([
    'common/services'
], function (Services) {

    var form;
    var layout;

    function updateImages() {
        layout.progressOn();
        return Services.readImagesFromCatalogToDb()
            .then(function (response) {
                layout.progressOff();
                dhtmlx.alert(
                    'Обработано товаров - ' + response.products
                    + '<br>Обработано изображений - ' + response.images + '.'
                );
            })
            .catch(function () {
                layout.progressOff();
            });
    }

    function updateProductsDescriptions() {
        layout.progressOn();
        return Services.readImagesFromCatalogToDb()
            .then(function (response) {
                layout.progressOff();
                dhtmlx.alert(
                    'Обработано товаров - ' + response.products
                    + '<br>Обработано изображений - ' + response.images + '.'
                );
            })
            .catch(function () {
                layout.progressOff();
            });
    }

    function init(app, tab) {
        layout = app.layout;
        form = tab.attachForm([
            {
                type: 'button',
                offsetLeft: 20,
                offsetTop: 20,
                name: 'update-images-to-db',
                value: 'Пересчитать изображения из каталога в базу данных'
            },
            {
                type: 'button',
                offsetLeft: 20,
                offsetTop: 20,
                name: 'update-products-descriptions-to-json',
                value: 'Пересчитать описание товаров в json формат'
            }
        ]);
        form.attachEvent('onButtonClick', function (name) {
            switch(name) {
                case 'update-images-to-db':
                    updateImages();
                    break;
                case 'update-products-descriptions-to-json':
                    updateProductsDescriptions();
                    break;
            }
        });
    }

    return {
        init: init
    };
});

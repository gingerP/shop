define([
    'common/dialog',
    'common/services'
], function (Dialog, Services) {
    'use strict';

    function render(id) {
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
                            console.warn('DOM object for id=' + item.id + ' was not rendered!');
                        }
                    });
                }
            })
            .catch(Dialog.error);
    }

    return {
        render: render
    };
});

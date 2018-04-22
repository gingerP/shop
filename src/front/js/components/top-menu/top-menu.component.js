(function () {
    var isOpened = false;
    var $panelLinks = $('.top-panel-links');
    var $panelOverlay = $('.top-panel-overlay');
    var $body = $(document.body);

    function updatePageScrolling() {
        if (isOpened) {
            $body.addClass('block-scrolling');
        } else {
            $body.removeClass('block-scrolling');
        }
    }

    function changeOpenedState() {
        isOpened = !isOpened;
        if (isOpened) {
            $panelOverlay.addClass('opened');
            $panelLinks.addClass('opened');
        } else {
            $panelOverlay.removeClass('opened');
            $panelLinks.removeClass('opened');
        }
        updatePageScrolling();
    }

    $('.top-panel-opener').on('click', changeOpenedState);
})();

(function () {
    var isOpened = false;
    var $panelLinks = $('.top-panel-links');
    var $panelOverlay = $('.top-panel-overlay');

    var $topPanelOpener = $('.top-panel-opener');
    var $topPanelCloser = $('.top-panel-closer');

    var $body = $(document.body);

    function updatePageScrolling() {
        if (isOpened) {
            $body.addClass('block-scrolling');
        } else {
            $body.removeClass('block-scrolling');
        }
    }

    function close() {
        isOpened = false;
        $panelOverlay.removeClass('opened');
        $panelLinks.removeClass('opened');
        updatePageScrolling();
    }


    $topPanelOpener.on('click', function () {
        isOpened = true;
        $panelOverlay.addClass('opened');
        $panelLinks.addClass('opened');
        updatePageScrolling();
    });

    $topPanelCloser.on('click', close);
    $panelOverlay.on('click', close);
})();

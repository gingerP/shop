(function () {
    var $categories = $('.categories-mosaic .categories-item');
    var windowWidthLimit = 1000;

    function processHeightToWightRatio() {
        function handleResize() {
            var windowWidth = $(window).width();
            $categories.each(function (index, category) {
                var $category = $(category);
                var data = $category.data();
                var heightToWidthRatio = data.heightToWidthRatio;
                if (windowWidth <= windowWidthLimit || !heightToWidthRatio) {
                    $category.css({height: '', overflow: '', display: ''});
                    return;
                }
                var width = $category.width();
                var height = width * heightToWidthRatio;
                $category.css({height: height + 'px', overflow: 'hidden', display: 'block'});
            });
        }

        if ($categories.length && $categories.data().heightToWidthRatio) {
            $(window).on('resize', handleResize);
        }
        handleResize();
    }

    processHeightToWightRatio();
})();

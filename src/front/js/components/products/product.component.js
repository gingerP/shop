$(document).ready(function () {
    var imageZoom;
    var imageGallery = new ImagesGallery()
        .init('.s_images>div', '.m_images img', '.big_img', '.viewport_images', '#gallery');

    function imageNamePrepare(inName, prefix) {
        var matches = /^(.*\/)+([^\/]+)$/g.exec(inName);
        var imagePath = matches[1];
        var imageName = /^.*(_.*)$/g.exec(matches[2])[1];
        return imagePath + prefix + imageName;
    }

    function zoomCallback(state) {
        if (state === 'zoom_in') {
            imageGallery.hideArrows();
        } else if (state === 'zoom_out') {
            imageGallery.showArrows();
        }
    }


    function initializeImagesViewInLine() {
        var $images = $('.s_images .image_preview');
        var $image = $images.first();
        var $doc = $(document);
        var $galleryViewport = $('.viewport');
        var imagesLineWidth = $image.outerWidth(true) * $images.length;

        function onResize() {
            if ($doc.width() < 1260) {
                $galleryViewport.css('width', imagesLineWidth + 'px');
            } else {
                $galleryViewport.removeAttr('style');
            }
        }
        $(window).resize(AuUtils.debounce(onResize, 300));
        onResize();
    }

    imageZoom = new ImageZoom().init('.squareX', '#main_gallery_image', imageNamePrepare, zoomCallback);

    imageGallery.current = 0;
    imageGallery.changeMainImage(imageGallery.current);
    imageGallery.attachEvent('imageChange', function (oldImage, newImage) {
        imageZoom.updatePreviewImage(imageNamePrepare(newImage, 'm'));
        imageZoom.updateZoomedImage(imageNamePrepare(newImage, 'l'));
    });

    initializeImagesViewInLine();

});

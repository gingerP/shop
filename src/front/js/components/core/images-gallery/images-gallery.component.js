function ImagesGallery() {
    this.config = {
        animationSpeed: 50
    };
}

ImagesGallery.prototype.init = function (previewImageSelector, zoomImageSelector, viewImgSelector, viewPort,
                                         mainContainer, scrolling) {
    this.current = 0;
    this.$previewImageSelector = $(previewImageSelector);
    this.$zoomImageSelector = $(zoomImageSelector);
    this.$viewImgSelector = $(viewImgSelector);
    this.$squarex = $('.squareX');
    this.$leftArrow = $('.gallery_left_arrow');
    this.$rightArrow = $('.gallery_right_arrow');
    this.indexLabelDom = document.getElementsByClassName('image-gallery-position-label');
    if (this.indexLabelDom.length) {
        this.indexLabelDom = this.indexLabelDom[0];
    }
    this.$viewPort = $(viewPort);
    this.$mainContainer = $(mainContainer);
    this.initEvents();
    this.scrolling = scrolling;
    if (this.scrolling) {
        this.updateScrollBarWidth();
        var a = this.$mainContainer.customScrollbar({vScroll: true, animationSpeed: 300});
    }
    this.events = {
        imageChange: []
    };
    return this;
};

ImagesGallery.prototype.attachEvent = function (eventName, event) {
    if (typeof(event) === 'function' && typeof(this.events[eventName]) !== 'undefined') {
        this.events[eventName].push(event);
        return true;
    }
    return false;
};

ImagesGallery.prototype.runEvents = function (eventName, _args) {
    if (typeof(this.events[eventName]) !== 'undefined' && this.events[eventName].length) {
        $.each(this.events[eventName], function (index, event) {
            event.apply(null, _args);
        });
    }
};

ImagesGallery.prototype.makeImageUnActive = function (index) {
    if (index > -1 && index <= mas.length) {
        var divs = document.getElementsByClassName('s_images');
        var imgsToUnActive = divs[index].getElementsByTagName('img');
        if (imgsToUnActive.length > 0) {
            $(imgsToUnActive[0]).removeClass('img_to_front');
            $(divs[index]).addClass('cursor_pointer');
            $(divs[index]).removeClass('review');
        }
    }
};

ImagesGallery.prototype.makeImageActive = function (index) {
    if (index > -1 && index <= mas.length) {
        var divs = document.getElementsByClassName('s_images');
        var imgsToActive = divs[index].getElementsByTagName('img');
        if (imgsToActive.length > 0) {
            $(divs[index]).removeClass('cursor_pointer');
            $(imgsToActive[0]).addClass('img_to_front');
            $(divs[index]).addClass('review');
            var zoom = document.getElementsByClassName('class')[0];
            $(zoom).css('backgroundImage', 'url()');

        }

    }
};

ImagesGallery.prototype.getImageIndexByClick = function (clickedElem, images) {
    if (images.length) {
        var separatorSmall = clickedElem.src.lastIndexOf('_');
        for (var i = 0; i < images.length; i++) {
            var separatorMedium = images[i].src.lastIndexOf('_');
            if (separatorMedium != -1
                && separatorSmall != -1
                && images[i].src.substr(separatorMedium + 1) == clickedElem.src.substr(separatorSmall + 1)) {
                return i;
            }
        }
    }
    return 0;
};

ImagesGallery.prototype.showZoomWin = function (index) {
    var divs = document.getElementsByClassName('image_item');
    if (divs.length > 0) {
        var element = divs[index].getElementsByClassName('zoom_win')[0];
        $(element).css('zIndex', 3);
        $(element).css('opacity', 0.1);
    }
};

ImagesGallery.prototype.hideZoomWin = function (index) {
    var divs = document.getElementsByClassName('image_item');
    if (divs.length > 0) {
        var element = divs[index].getElementsByClassName('zoom_win')[0];
        $(element).css('zIndex', -1);
        $(element).css('opacity', 0);
    }
};

ImagesGallery.prototype._getMainGalleryImage = function _getMainGalleryImage() {
    if (!this._mainGalleryImage) {
        this._mainGalleryImage = document.getElementById('main_gallery_image');
    }
    return this._mainGalleryImage;
};

ImagesGallery.prototype._getImageEffect = function _getImageEffect() {
    if (!this._imgEffect) {
        this._imgEffect = document.getElementById('img_effect');
    }
    return this._imgEffect;
};

ImagesGallery.prototype._getImages = function _getImages() {
    if (!this._images) {
        this._images = document.querySelectorAll('.s_images .image_preview') || [];
    }
    return this._images;
};

ImagesGallery.prototype._getImagesInfo = function _getImagesInfo() {
    if (!this._imagesInfo) {
        this._imagesInfo = $.map(
            this._getImages(),
            function (image) {
                return $(image).data();
            }
        );
    }
    return this._imagesInfo;
};

ImagesGallery.prototype.changeMainImage = function (to) {
    var self = this;
    var mainImg = self._getMainGalleryImage();
    var effectImg = self._getImageEffect();
    var imagesInfo = self._getImagesInfo();
    var newSelectedImageInfo = imagesInfo[to];
    var $smallBC = $('.s_images .blackout');
    if ($smallBC.length > to) {
        $($smallBC).removeClass('gallery_selected');
        $($smallBC[to]).addClass('gallery_selected');
    }
    if (this.scrolling) {
        this.$mainContainer.customScrollbar('scrollTo', $smallBC[to]);
    }

    var oldImageSrc = effectImg.src;
    effectImg.src = newSelectedImageInfo.imageMedium;
    $(effectImg)
        .stop()
        .hide()
        .fadeIn(500, function () {
                mainImg.src = newSelectedImageInfo.imageMedium;
                $('big_img').css('backgroundImage', 'url(' + newSelectedImageInfo.imageMedium + ')');
                $('#img_effect').opacity = 0;
                $(document.getElementsByClassName('zoom_image')[0]).css('zIndex', 5);
                self.runEvents('imageChange', [oldImageSrc, mainImg.src]);
                setTimeout(function () {
                    $(mainImg).css('opacity', '1');
                }, 1000);
            }
        );
    if (this.indexLabelDom) {
        this.indexLabelDom.innerHTML = to + 1 + ' / ' + $smallBC.length;
    }
};

ImagesGallery.prototype.initEvents = function () {
    var self = this;
    $(this.$previewImageSelector).click(
        function () {
            var clickedImg = $('img', this);
            if (clickedImg[0].id !== 'item') {
                var next = self.getImageIndexByClick(clickedImg[0], $('img', self.$previewImageSelector));
                if (self.current !== next) {
                    self.current = next;
                    self.changeMainImage(self.current);
                }
            }
        }
    );

    this.$leftArrow.children('div')
        .click(
            function () {
                self.selectPrevImage();
            }
        );
    this.$rightArrow.children('div')
        .click(
            function () {
                self.selectNextImage();
            }
        );

    $('body').keydown(function (e) {
        if (e.keyCode == 37) { // left
            self.selectPrevImage();
        } else if (e.keyCode == 39) { // right
            self.selectNextImage();
        }
    });

};

ImagesGallery.prototype.updateScrollBarWidth = function () {
    var $imageContainers = $('.s_images.overview>div', this.$mainContainer);
    var imagesCount = $imageContainers.length;
    var overViewWidth = 0;
    for (var containerIndex = 0; containerIndex < imagesCount; containerIndex++) {
        overViewWidth += $imageContainers[containerIndex].offsetWidth;
    }
    $('.s_images.overview', this.$mainContainer).css('width', overViewWidth);
};

ImagesGallery.prototype.selectNextImage = function () {
    if (this.current < this.$previewImageSelector.length - 1) {
        this.current++;
    } else {
        this.current = 0;
    }
    this.changeMainImage(this.current);
};

ImagesGallery.prototype.selectPrevImage = function () {
    if (this.current > 0) {
        this.current--;
    } else {
        this.current = this.$previewImageSelector.length - 1;
    }
    this.changeMainImage(this.current);
};

ImagesGallery.prototype.showArrows = function () {
    this.$leftArrow.fadeIn(this.config.animationSpeed);
    this.$rightArrow.fadeIn(this.config.animationSpeed);
};

ImagesGallery.prototype.hideArrows = function () {
    this.$leftArrow.fadeOut(this.config.animationSpeed);
    this.$rightArrow.fadeOut(this.config.animationSpeed);
};

ImagesGallery.prototype.onSwipe = function (callback) {

    var element = document.getElementsByClassName('squareX');
    if (!element.length) {
        console.warn('Element to swipe not found');
        return;
    }
    var touchsurface = element[0];
    var swipedir;
    var startX;
    var startY;
    var distX;
    var distY;
    var threshold = 150; //required min distance traveled to be considered swipe
    var restraint = 100; // maximum distance allowed at the same time in perpendicular direction
    var allowedTime = 300; // maximum time allowed to travel that distance
    var elapsedTime;
    var startTime;
    var handleswipe = callback || function (swipedir) {
        };

    touchsurface.addEventListener('touchstart', function (e) {
        var touchobj = e.changedTouches[0];
        swipedir = 'none';
        startX = touchobj.pageX;
        startY = touchobj.pageY;
        startTime = new Date().getTime(); // record time when finger first makes contact with surface
        //e.preventDefault();
    }, false);

    touchsurface.addEventListener('touchmove', function (e) {
        var touchobj = e.changedTouches[0];
        var x = touchobj.pageX;
        var y = touchobj.pageY;
        var isVerticalPercent = ( Math.abs(startY - y) / Math.abs(startX - x)) * 100;
        console.info(isVerticalPercent);
        if (isVerticalPercent < 80) {
            e.preventDefault() // prevent scrolling when inside DIV
        }
    }, false);

    touchsurface.addEventListener('touchend', function (e) {
        var touchobj = e.changedTouches[0];
        distX = touchobj.pageX - startX; // get horizontal dist traveled by finger while in contact with surface
        distY = touchobj.pageY - startY; // get vertical dist traveled by finger while in contact with surface
        elapsedTime = new Date().getTime() - startTime; // get time elapsed
        if (elapsedTime <= allowedTime) { // first condition for awipe met
            if (Math.abs(distX) >= threshold && Math.abs(distY) <= restraint) { // 2nd condition for horizontal swipe met
                swipedir = (distX < 0) ? 'left' : 'right'; // if dist traveled is negative, it indicates left swipe
            }
            else if (Math.abs(distY) >= threshold && Math.abs(distX) <= restraint) { // 2nd condition for vertical swipe met
                swipedir = (distY < 0) ? 'up' : 'down'; // if dist traveled is negative, it indicates up swipe
            }
        }
        handleswipe(swipedir);
        //e.preventDefault();
    }, false);
};

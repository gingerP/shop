ImageZoom = function() {
    this.cfg = {
        width: 570,
        height: 800,
        cursorWidth: 200,
        cursorHeight: 334,
        zoomedImageWidth: 1200,
        zoomedImageHeight: 2000,
        previewImageWidth: 420,
        previewImageHeight: 700,
        position: 'left',
        animationSpeed: 100,
        padding: 100,
        offsetXsquareX: -8,
        offsetYsquareX: -8
    };
    this.ids = {
        zoomSwitcher: 'zoom_switcher'
    };
};

ImageZoom.prototype.init = function(viewPortSelector, viewImageSelector, imageNamePreparator, stateListener) {
    this.$viewPort = $(viewPortSelector);
    this.$viewImage = $(viewImageSelector);
    this.imageNamePreparator = typeof(imageNamePreparator) == 'function'? imageNamePreparator: function(data) { return data; };
    this.stateListener = stateListener;
    this.obj = {};
    this.obj.$zoomSwitcher = undefined;
    this.obj.$zoomWindowContainer = undefined;
    this.obj.$zoomCursor = undefined;
    this.zoomInState = false;
    this.zoomActive = false;
    this.initZoomSwitcher();
    this.__init();
    this.initEvents();
    this.initPosition();
    var thiz = this;
    keyBoard.handle(27, function() {
        thiz.zoomInState = false;
        thiz.makeZoomState();
    })
    return this;
};

ImageZoom.prototype.createCenteredContainer = function(content) {
    var template = '\
    <div style="height: 100%;">\
        <div style="height: 100%;display: table;">\
            <div style="display: table-cell; vertical-align: middle;">\
                <div>\
                    {content}\
                </div>\
            </div>\
        </div>\
    </div>\
    ';
    template = template.replace('{content}', content);
    return template;
}

ImageZoom.prototype.__init = function() {
    var mainTemplate = '\
                <div id="{id}"class="zoom_image">\
                    <div class="full_screen_blocker"></div>\
                    <div class="zoom_elements_container" style="height: 100%;">\
                        <div style="display: inline-block;height: 100%;margin: auto;">\
                            <div style="float: left; height: 100%;">{preview}</div>\
                            <div style="float: left; height: 100%;">{zoom}</div>\
                            <div style="float: left; height: 100%;">{close_button}</div>\
                        </div>\
                    </div>\
                </div>';
    var previewTemplate = '\
                <div class="zoom_preview_container">\
                    <div>\
                        <div class="zoom_cursor"></div>\
                        <img src="">\
                    </div>\
                </div>';
    var zoomTemplate = '\
                <div class="zoom_window_container">\
                    <div>\
                        <img src="">\
                    </div>\
                </div>';
    var closeButtonTemplate = '\
                <div class="zoom_close_button_container">\
                    <div class="zoom_close_button">\
                        <img src="/images/cross.png">\
                    </div>\
                </div>';
    var readyPreviewTemplate = this.createCenteredContainer(previewTemplate);
    var readyZoomTemplate = this.createCenteredContainer(zoomTemplate);
    var readyCloseButtonTemplate = this.createCenteredContainer(closeButtonTemplate);
    mainTemplate = mainTemplate.replace('{preview}', readyPreviewTemplate).replace('{zoom}', readyZoomTemplate).replace('{close_button}', readyCloseButtonTemplate);


    this.objId = U.getRandomString();
    mainTemplate = mainTemplate.replace('{id}', this.objId);

    var tmp = document.createElement('div');
    tmp.innerHTML = mainTemplate;
    document.body.appendChild(tmp.children[0]);

    this.obj.$main = $('#' + this.objId);
    this.obj.$zoomWindowContainer = $('.zoom_window_container', this.obj.$main);
    this.obj.$zoomWindowImage = $('img', this.obj.$zoomWindowContainer);
    this.obj.$zoomCursor = $('.zoom_cursor', this.obj.$main);
    this.obj.$zoomElementsContainer = $('.zoom_elements_container', this.obj.$main);
    this.obj.$zoomPreviewContainer = $('.zoom_preview_container', this.obj.$main);
    this.obj.$zoomPreviewImage = $('img', this.obj.$zoomPreviewContainer);
    this.obj.$blocker = $('.full_screen_blocker', this.obj.$main);

    var viewPortParams = this.sizes().viewPort;
    this.obj.$zoomElementsContainer.css('width', /*viewPortParams.size.width*/ '100%');
    this.obj.$zoomElementsContainer.css('height', /*viewPortParams.size.height*/ '100%');

    var zoomParams = this.sizes().zoom;
    this.obj.$zoomWindowContainer.css('width', zoomParams.size.width);
    this.obj.$zoomWindowContainer.css('height', zoomParams.size.height);

    this.obj.$closeButtonContainer = $('.zoom_close_button_container', this.obj.$main);
    this.obj.$closeButtonContainer.css('height', zoomParams.size.height);
    this.obj.$closeButton = $('.zoom_close_button', this.obj.$closeButtonContainer);
    this.obj.$closeButtonImage = $('img', this.obj.$closeButton);

    var previewParams = this.sizes().preview;
    this.obj.$zoomPreviewContainer.css('width', previewParams.size.width);
    this.obj.$zoomPreviewContainer.css('height', previewParams.size.height);
    this.cfg.previewImageWidth = previewParams.size.width;
    this.cfg.previewImageHeight = previewParams.size.height;


    var viewportCursorRatio = 3;
    this.cfg.cursorWidth = (previewParams.size.width * zoomParams.size.width / this.cfg.zoomedImageWidth);
    this.cfg.cursorHeight = (previewParams.size.height * zoomParams.size.height / this.cfg.zoomedImageHeight);

    this.obj.$zoomCursor.css('width', this.cfg.cursorWidth);
    this.obj.$zoomCursor.css('height', this.cfg.cursorHeight);

    this.obj.$zoomPreviewImage.css('width', previewParams.size.width);
    this.obj.$zoomPreviewImage.css('height', previewParams.size.height);

    //inputHoverModule.inn().updateHover(this.obj.$closeButton);

    return this;
};

ImageZoom.prototype.debounce = function (callback, timeout) {
    var debounceTimeout;
    return function () {
        var args = arguments;
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(function () {
            callback.call(null, args);
        }, timeout);
    };
};

ImageZoom.prototype.initZoomSwitcher = function(){
    var switcher = document.createElement('div');
    switcher.setAttribute('class', 'f-17 icon_viewPort');
    switcher.setAttribute('id', this.ids.zoomSwitcher);
    var switcherIcon = document.createElement('div');
    switcherIcon.setAttribute('class', this.zoomInState? 'zoom_out icon visible': 'zoom_in icon visible');
    switcher.appendChild(switcherIcon);
    switcherIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" fill="#414141" height="36" viewBox="0 0 24 24" width="36">\
        <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>\
        <path d="M0 0h24v24H0V0z" fill="none"/>\
        <path d="M12 10h-2v2H9v-2H7V9h2V7h1v2h2v1z"/>\
        </svg>';
    this.$viewPort.append(switcher);
    this.obj.$zoomSwitcher = $(switcher);
};

ImageZoom.prototype.withZoomWindowConfig = function(config) {
    config = config || {};
    $.extend(true, this.cfg, config);
    return this;
};

ImageZoom.prototype.initEvents = function() {
    var thiz = this;
    this.obj.$zoomSwitcher.mouseup(
        function(e) {
            thiz.zoomInState = !thiz.zoomInState;
            thiz.obj.$zoomSwitcher.find('.zoom_in').removeClass('zoom_in').addClass('zoom_out');

            thiz.updateZoomCursorPosition(U.extractRelativePosition(e, thiz.$viewPort));
            var handlers = {
                imageLoaderCallback:  function() {
                    thiz.updateZoomedImagePosition(U.extractRelativePosition(e, thiz.$viewPort));
                }
            };
            thiz.makeZoomState(handlers);
        }
    );

    this.obj.$closeButton.hover(
    /*    function() {
            TweenLite.to(thiz.obj.$closeButtonImage[0], 0.2, {rotation: 90});
        },
        function() {
            TweenLite.to(thiz.obj.$closeButtonImage[0], 0.2, {rotation: 0});
        }*/
    );
    this.obj.$closeButton.mouseup(function() {
        thiz.zoomInState = !thiz.zoomInState;
        thiz.makeZoomState();
    });

    $('>div', this.obj.$zoomPreviewContainer)
        .hover(
            function(e) {
                thiz.zoomActive = true;
            },
            function() {
                /*thiz.zoomActive = false;
                thiz.zoomInState = false;
                thiz.makeZoomState();*/
            }
        ).mousemove(
            function(e) {
                if (thiz.zoomInState) {
                    var pos = U.extractRelativePosition(e, this)
                    var cursorPosition = thiz.updateZoomCursorPosition(pos);
                    thiz.updateZoomedImagePosition(cursorPosition);
                }
            }
        )
};

ImageZoom.prototype.initPosition = function() {

};

ImageZoom.prototype.updateZoomCursorPosition = function(_pos) {
    var pos = this.getRecPosition(
        _pos
        , {
            x: this.cfg.previewImageWidth,
            y: this.cfg.previewImageHeight
        }, {
            x: this.cfg.cursorWidth,
            y: this.cfg.cursorHeight
        }
    );
    this.obj.$zoomCursor.css('left', pos.x);
    this.obj.$zoomCursor.css('top', pos.y);
    return pos;
};

ImageZoom.prototype.updateZoomedImagePosition = function(cursorPosition) {
    /*var pos = this.getRecPosition(
        {
            x: this.cfg.zoomedImageWidth * pos.x / this.cfg.previewImageWidth,
            y: this.cfg.zoomedImageHeight * pos.y / this.cfg.previewImageHeight
        }, {
            x: this.cfg.zoomedImageWidth,
            y: this.cfg.zoomedImageHeight
        }, {
            x: this.cfg.width,
            y: this.cfg.height
        }
    )*/

    var ratio = this.cfg.zoomedImageHeight / this.cfg.previewImageHeight;
    this.obj.$zoomWindowImage.stop();
    this.obj.$zoomWindowImage.css('left', - (cursorPosition.x * ratio) + 'px');
    this.obj.$zoomWindowImage.css('top', - (cursorPosition.y * ratio) + 'px');
};

ImageZoom.prototype.makeZoomState = function(handlers) {
    this[this.zoomInState? '_makeZoomInState': '_makeZoomOutState'](handlers);
    return this;
};

ImageZoom.prototype._makeZoomInState = function(handlers) {
    var thiz = this;
    this.stateListener('zoom_in');
    this.updatePreviewImage(this.$viewImage[0].src, handlers);
    this.updateZoomedImage(this.imageNamePreparator(this.$viewImage[0].src, 'l'), handlers);
    this.obj.$main.fadeIn(this.cfg.animationSpeed, function() {
        var pos = {x: 1000, y: 0};
        var cursorPosition = thiz.updateZoomCursorPosition(pos);
        thiz.updateZoomedImagePosition(cursorPosition);
    });
    if (this.obj.$zoomSwitcher.find('.zoom_in').length) {
        this.obj.$zoomSwitcher.find('.zoom_in').removeClass('zoom_in').addClass('zoom_out');
    }
    return this;
};

ImageZoom.prototype._makeZoomOutState = function() {
    if (this.obj.$zoomSwitcher.find('.zoom_out').length) {
        this.obj.$zoomSwitcher.find('.zoom_out').removeClass('zoom_out').addClass('zoom_in');
    }
    this.stateListener('zoom_out');
    this.obj.$main.fadeOut(this.cfg.animationSpeed);
    return this;
};

ImageZoom.prototype.getRecPosition = function(pos, viewPortSize, recSize) {
    var newPos = {
        x: 0,
        y: 0
    }

    if (pos.x > recSize.x / 2 && pos.x < viewPortSize.x - (recSize.x / 2)) {
        newPos.x = pos.x - (recSize.x / 2);
    } else if (pos.x <= recSize.x / 2 && pos.x >= 0) {
        newPos.x = 0
    } else if (pos.x <= viewPortSize.x && pos.x >= (viewPortSize.x - (recSize.x / 2)) || pos.x > viewPortSize.x) {
        newPos.x = viewPortSize.x - recSize.x;
    }

    if (pos.y > recSize.y / 2 && pos.y < viewPortSize.y - (recSize.y / 2)) {
        newPos.y = pos.y - (recSize.y / 2);
    } else if (pos.y <= recSize.y / 2 && pos.y >= 0) {
        newPos.y = 0
    } else if (pos.y <= viewPortSize.y && pos.y >= (viewPortSize.y - (recSize.y / 2)) || pos.y > viewPortSize.y) {
        newPos.y = viewPortSize.y - recSize.y;
    }

    //console.info('newPos: ' + newPos.x.toFixed(2) + '/' + newPos.y.toFixed(2) + ' pos: ' + pos.x.toFixed(2) + '/' + pos.y.toFixed(2) + ' viewPortSize: ' + viewPortSize.x.toFixed(2) + '/' + viewPortSize.y.toFixed(2) + ' recSize: ' + recSize.x.toFixed(2) + '/' + recSize.y.toFixed(2) + ' genSize: ' + genSize.x.toFixed(2) + '/' + genSize.y.toFixed(2));
    return newPos;
};

ImageZoom.prototype.updatePreviewImage = function(src, handlers) {
    if (!this.obj.$zoomPreviewImage) return;
    this.obj.$zoomPreviewImage[0].src = src;
};

ImageZoom.prototype.updateZoomedImage = function(src, handlers) {
    if (!this.obj.$zoomWindowContainer) return;
    var thiz = this;
    var $image = this.obj.$zoomWindowContainer.find('img');
    $image.attr('src', src);
    $image.imageloader({
        callback: function (elm) {
            if (typeof (handlers || {}).imageLoaderCallback == 'function') {
                handlers.imageLoaderCallback();
            }
        }
    });
};

ImageZoom.prototype.sizes = function() {
    var viewPortRatio = 1.6667;
    var zoomRatio = 0.8;
    var previewRatio = 0.8;
    /*var absoluteHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;*/
    var absoluteHeight = screen.height;
    console.info();
    function formatter(_height, _width) {
        return {size: {width: _width, height: _height}};
    }
    return {
        preview: (function() {
            var height = 0;
            var width = 0;
            if (absoluteHeight * previewRatio < 700) {
                height = absoluteHeight * previewRatio;
                width = height / viewPortRatio;
            } else {
                height = 700;
                width = 420;
            }
            return formatter(height, width);
        })(),
        zoom: (function() {
            var height = absoluteHeight * zoomRatio;
            var width = (height / viewPortRatio);
            return formatter(height, width);
        })(),
        viewPort: (function() {
            var previewZoomDistance = absoluteHeight * 0.1;
            var previewZoomRatio = 1.2;
            var height = absoluteHeight * 0.7;
            var width = (height / viewPortRatio) + (height / viewPortRatio) * previewZoomRatio + previewZoomDistance;
            return formatter(height, width);
        })()
    }
}
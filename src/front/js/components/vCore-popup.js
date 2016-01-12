Popup = function() {};

Popup.prototype.init = function(containerSelector, showCallback, hideCallback, isFixed) {
    this.isFixed = isFixed || false;
    this.containerSelector = containerSelector;
    this.$container = $(this.containerSelector);
    if (this.$container.length) {
        this.domID = undefined;
        this.dataContainer = undefined;
        this.absolutePositions = {};
        this.mouse_is_inside = false;
        this.isVisible = false;
        this.manualHide = false;
        var dom = this.createDOM();
        this.showCallback = showCallback;
        this.hideCallback = hideCallback;
        var $containerObject = undefined;
        if (this.isFixed) {
            $containerObject = $('body');
            $containerObject.append(dom);
        } else {
            $containerObject = $(containerSelector);
            $containerObject.after(dom);
        }
        this.$dom = $(dom);
        this.$popupBlock = $('.popup_block');
        this.initEvents();
    }
    return this;
};

Popup.prototype.createDOM = function() {
    var mainDiv = document.createElement('DIV');
    this.domID = U.getRandomString();
    mainDiv.setAttribute('id', this.domID);
    mainDiv.setAttribute('class', this.isFixed? 'popup_fixed': 'popup_relative');
    var divPopupArrow = document.createElement('DIV');
    divPopupArrow.setAttribute('class', 'popup_arrow');
    var divPopupBlock = document.createElement('DIV');
    divPopupBlock.setAttribute('class', 'popup_block');
    mainDiv.style.display = 'none';
    mainDiv.style.zIndex = 100;
    mainDiv.appendChild(divPopupBlock);
    mainDiv.appendChild(divPopupArrow);
    this.dataContainer = divPopupBlock;
    return mainDiv;
};

Popup.prototype.initEvents = function() {
    var thiz = this;
    this.$container.on('click', function(e) {
        thiz.mouse_is_inside = true;
        if (thiz.isVisible) {
            thiz.hide();
        } else {
            thiz.show();
        }
    });

    this.$container.hover(function(){
        thiz.mouse_is_inside = true;
    }, function(){
        thiz.mouse_is_inside = false;
    }
    )
    this.$dom.hover(function(){
        thiz.mouse_is_inside = true;
    }, function(){
        thiz.mouse_is_inside = false;
    });

    $('body').click(function(){
        if(!thiz.mouse_is_inside && !thiz.manualHide) {
            thiz.hide();
        }
    });
};

Popup.prototype.getPopupPosition = function() {
    var rect = this.$container[0].getBoundingClientRect();
    var pos = {
        popup:
        {   x: undefined,
            y: undefined},
        arrow:
        {   x: undefined,
            y: undefined}
    };
    var windowWidth = $(window).width();
    if (this.isFixed) {
        if (!U.isNumber(this.absolutePositions.right)) {
            if (this.$popupBlock[0].getBoundingClientRect().right >= windowWidth) {
                pos.popup.x = windowWidth - rect.right;
            } else {
                pos.popup.x = rect.left + ((rect.right - rect.left) / 2) - 30;
            }
        } else {
            pos.popup.x = this.absolutePositions.right;
        }
        pos.popup.y = rect.bottom - 5;

        pos.arrow.x = rect.left + ((rect.right - rect.left) / 2) - 10 - pos.popup.x;
        pos.arrow.y = rect.bottom - pos.popup.y - 4;
    } else {


        pos.popup.x = 0;
        pos.popup.y = 5;
        pos.arrow.x = rect.width / 2 - 20;
        pos.arrow.y = -5;

        var containerPosRelativelyParent = this.$container.position();

        if (!U.isNumber(this.absolutePositions.right)) {
            if (containerPosRelativelyParent.left >= windowWidth) {
                pos.popup.x = windowWidth - rect.right;
            } else {
                pos.popup.x = containerPosRelativelyParent.left - 30;
            }
        } else {
            pos.popup.x = containerPosRelativelyParent.left;
        }
        pos.popup.y = containerPosRelativelyParent.top + rect.height - 5;

        pos.arrow.x = rect.width / 2 + 30 - 10;
        pos.arrow.y = pos.popup.y;
    }
    return pos;
};

Popup.prototype.withPositions = function(options) {
    this.absolutePositions = {};
    $.extend(true, this.absolutePositions, options || {});
    return this;
};

Popup.prototype.show = function() {
    var pos = this.getPopupPosition();
    if (this.isVisible === false) {
        if (U.isNumber(pos.popup.x) && U.isNumber(pos.popup.y)) {
            this.$dom.css('left', pos.popup.x + 'px');
            this.$dom.css('top', pos.popup.y + 'px');
        }
        if (U.isNumber(pos.arrow.x) && U.isNumber(pos.arrow.y)) {
            $('.popup_arrow', this.$dom).css('left', pos.arrow.x + 'px');
        }
        if (typeof(this.showCallback) == 'function') {
            this.showCallback(this);
        }
        //this.dom.style.display = 'block';
        this.isVisible = true;
        this.$container.addClass('input_hover_untouchable');
        this.$dom.finish();
        this.$dom.fadeIn();
    }
};

Popup.prototype.hide = function() {
    if (this.isVisible === true) {
        //this.dom.style.display = 'none';
        this.isVisible = false;
        this.$container.removeClass('input_hover_untouchable');
        this.$dom.finish();
        this.$dom.fadeOut(this.hideCallback);
    }
};

Popup.prototype.loadDataWithAjax = function(serviceMethod, formatData, callback) {
    var thiz = this;
    $.ajax({
        dataType: 'json',
        url: "/api/" + serviceMethod,
        context: document.body
    }).done(function(data) {
        if (typeof(formatData) != 'undefined') {
            thiz.setData(formatData(data), callback);
        }
    });
};

Popup.prototype.setData = function(dataDOM, callback) {
    if (typeof(this.dataContainer) != 'undefined') {
        this.dataContainer.innerHTML = '';
        if (typeof(dataDOM) == 'string') {
            this.dataContainer.innerHTML = dataDOM;
        } else if (U.hasContent(dataDOM) && typeof(dataDOM) == 'object') {
            this.dataContainer.appendChild(dataDOM);
        }

    }
    if (typeof(callback) == 'function') {
        callback();
    }
};

Popup.prototype.withManualHide = function() {
    this.manualHide = true;
    return this;
}



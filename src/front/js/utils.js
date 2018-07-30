$.fn.hasClasses = function (classes) {
    for (var i = 0, len = classes.length; i < len; i++) {
        if (this.hasClass(classes[i])) {
            return true;
        }
    }
    return false;
};

window.AuUtils = {
    extractRelativePosition: function extractRelativePosition(event, thiz) {
        var parentOffset = $(thiz).offset();
        var x = event.pageX - parentOffset.left;
        var y = event.pageY - parentOffset.top;
        return {x: x, y: y};
    },

    getRandomString: function getRandomString() {
        return Math.random().toString(36).slice(2);
    },

    isBase64: function isBase64(string) {
        return /^([A-Za-z0-9+/]{4})*([A-Za-z0-9+/]{4}|[A-Za-z0-9+/]{3}=|[A-Za-z0-9+/]{2}==)$/g.test(string);
    },

    isArray: function isArray(arg) {
        return Object.prototype.toString.call(arg) === '[object Array]';
    },

    isObject: function isObject(object) {
        return object !== null && typeof object === 'object';
    },

    hasContent: function hasContent(obj) {
        if (typeof(obj) === 'string') {
            return obj !== null && obj !== '';
        } else if (obj !== null && this.isArray(obj)) {
            return obj.length > 0;
        } else if (typeof(obj) !== 'undefined') {
            return obj !== null;
        }
        return false;
    },

    isNumber: function isNumber(data) {
        return !isNaN(data);
    },

    getPageCenter: function getPageCenter() {
        return {
            x: $(window).scrollLeft() + $(window).width() / 2,
            y: $(window).scrollTop() + $(window).height() / 2
        };
    },
    addHandlebarScript: function addHandlebarScript(name, content) {
        var script = document.createElement('script');
        document.head.appendChild(script);
        script.setAttribute('id', name);
        script.setAttribute('type', 'text/x-handlebars-template');
        script.innerHTML = content;
    },
    compilePrefillHandlebar: function compilePrefillHandlebar(handlerBarId, entity) {
        var string = Handlebars.compile($('#' + handlerBarId).html())(entity);
        return this.getDOMFromString(string);

    },
    getDOMFromString: function getDOMFromString(string) {
        var dom = document.createElement('DIV');
        dom.innerHTML = string;
        return dom.children[0];
    },
    dhtmlxDOMPreInit: function dhtmlxDOMPreInit() {
        if (arguments.length) {
            for (var argIndex = 0; argIndex < arguments.length; argIndex++) {
                arguments[argIndex].className += ' dhtmlx_dom';
            }
        }
    },
    debounce: function debounce(callback, timeout) {
        var debounceTimeout;
        return function () {
            var args = arguments;
            var self = this;
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(function () {
                callback.call(self, args);
            }, timeout);
        };
    },
    loadScriptAsync: function loadScriptAsync(uri, callback) {
        var tag = document.createElement('script');
        tag.src = uri;
        tag.async = true;
        tag.onload = callback;
        var firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
    }
};

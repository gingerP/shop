URL_PARAMS = {};
(window.onpopstate = function () {
    var match,
        pl = /\+/g,  // Regex for replacing addition symbol with a space
        search = /([^&=]+)=?([^&]*)/g,
        decode = function (s) {
            return decodeURIComponent(s.replace(pl, " "));
        },
        query = window.location.search.substring(1);

    URL_PARAMS = {};
    while (match = search.exec(query))
        URL_PARAMS[decode(match[1])] = decode(match[2]);
})();

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

    isIE: function isIE() {
        return navigator.userAgent.match(/MSIE/i) ? true : false;
    },

    isIE9: function isIE9() {
        return navigator.userAgent.match(/MSIE 9/i) ? true : false;
    },

    isOldIe: function isOldIe() {
        return navigator.userAgent.match(/MSIE/i)
        && !navigator.userAgent.match(/MSIE 9/i) && !navigator.userAgent.match(/MSIE 1/i) ? true : false;
    },

    isArray: function isArray(arg) {
        return Object.prototype.toString.call(arg) === '[object Array]';
    },

    isObject: function isObject(object) {
        return object !== null && typeof object === 'object';
    },

    getModifiedCurrentUrl: function getModifiedCurrentUrl(parameters) {
        var urlObj = AuUtils.getUrlAsObject(document.URL);
        for (var key in parameters) {
            urlObj.params[key] = parameters[key] + '';
        }
        return AuUtils.getUrlStringFromUrlObject(urlObj);
    },

    getParamFromCurrentUrl: function getParamFromCurrentUrl(key) {
        var urlObj = AuUtils.getUrlAsObject(document.URL);
        return urlObj.params[key];
    },

    getUrlAsObject: function getUrlAsObject(url) {
        var urlObj = {
            host: '',
            params: {}
        };
        var url_ = url.split('?');
        if (url_.length === 2) {
            urlObj.host = url_[0];
            var params = url_[1].split('&');
            for (var paramIndex = 0; paramIndex < params.length; paramIndex++) {
                var keyValuePair = params[paramIndex].split('=');
                var value = keyValuePair.length === 2 ? keyValuePair[1] : '';
                urlObj.params[keyValuePair[0]] = value;
            }
        }
        return urlObj;
    },

    getUrlStringFromUrlObject: function getUrlStringFromUrlObject(urlObj) {
        var url = '';
        if (typeof(urlObj) !== 'undefined') {
            url += urlObj.host || '';
            url += '?';
            if (urlObj.hasOwnProperty('params')) {
                for (var key in urlObj.params) {
                    url += key + '=' + urlObj.params[key] + '&';
                }
            }
        } else {
            console.warn('Incorrect url object passed into parameters: ' + JSON.stringify(urlObj));
        }
        return url;
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

    makeFirstCapitalLetter: function makeFirstCapitalLetter(string) {
        if (string !== undefined && string !== null) {
            return string.charAt(0).toUpperCase() + string.substr(1, string.length - 1);
        }
    },

    appendShadow: function appendShadow(mainDiv) {
    },

    extractBoxShadowColor: function extractBoxShadowColor(style) {
        var res = /((rgb|rgba){1}\(\d{0,3},\s\d{0,3},\s\d{0,3}\))/g.match(style);
        if (res && res.length > 2) {
            return res[1];
        }
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
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(function () {
                callback.call(null, args);
            }, timeout);
        };
    }
};

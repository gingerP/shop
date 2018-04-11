var APP = {
    COLOR_ACTIVE: '#808080',
    COLOR_UN_ACTIVE: '#88cc55',
    COLOR_MOUSE_DOWN: '#737373',
    SIDEBAR_WIN_WIDTH_POINT: '1280'
};

if (typeof(console) == 'undefined') {
    var console = (function () {
        return {
            info: function () {
            },
            log: function () {
            },
            warn: function () {
            }
        }
    })();
}

var params = {
    CHECK_UR: 'check_ur',
    CHECK_FIZ: 'check_fiz',
    PAGE_NAME: 'page_name',
    KEY: 'key',
    PAGE_ID: 'page_id',
    PAGE_NUM: 'page_num',
    SEARCH_VALUE: 'search_value',
    HIGH_LIGHT_ELEMENT: 'high_light_element',
    VIEW_MODE: 'view_mode',
    ITEMS_COUNT: 'items_count',
    PAGE__SINGLE_ITEM: 'singleItem',
    PAGE__CATALOG: 'catalog',
    PAGE__MAIN: 'main',
    PAGE__CONTACTS: 'contacts',
    PAGE__SEARCH: 'search'
};

var mas = []; // массив картинок
var to = 0;  // Счетчик, указывающий на текущую картинки
var step = 0;
var numberMode = false;
var viewMode = false;
var keys_GET = new Array();
var values_GET = new Array();
var constants = {HIGH_LIGHT_ELEMENT: 'high_light_element'}
var _colors = {
    dark: '#88cc55',
    mid: '#32CD32',
    light: '#ade681',
    font: '#414141'
};

function addParameterToUrl(parameter, value, url) {
    var url = new String(url);
    GET_();
    var signBegin = keys_GET.length == 0 ? '?' : '&';
    console.info('ADD ' + signBegin + parameter + '=' + value);
    return url.concat(signBegin + parameter + '=' + value);
}

function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
    var results = regex.exec(window.location.search);
    return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

function GET_() {
    GET(window.location.href);
}

function GET(url) {
    var adress = url.split("?");
    keys_GET.length = 0;
    values_GET.length = 0;
    console.info('begin');
    if (adress.length == 2) {
        var paramPairs = adress[1].split("&");
        for (index = 0; index < paramPairs.length; index++) {
            var keyValue = paramPairs[index].split("=");
            keys_GET[index] = keyValue[0];
            values_GET[index] = keyValue[1];
        }
    }
    console.info('LEN: ' + keys_GET.length);
}

if (document.getElementsByClassName == undefined) {
    document.getElementsByClassName = function (cl) {
        var retnode = [];
        var myclass = new RegExp('\\b' + cl + '\\b');
        var elem = this.getElementsByTagName('*');
        for (var i = 0; i < elem.length; i++) {
            var classes = elem[i].className;
            if (myclass.test(classes)) {
                retnode.push(elem[i]);
            }
        }
        return retnode;
    }
}

function closeTree(liObject) {
    var liLlst = liObject.getElementsByTagName('li');
    if (liLlst.length !== 0) {
        $(liObject.childNodes[1]).css({"background-image": arrows[0]});
    }
}


/*------------------------------------------gallery end---------------------------------------*/

function initBlackoutLogic() {
    var opacity = 0.2;
    var no_opacity = 0;
    var animation_speed = 200;
    $('.blackout').hover(
        function () {
            if ($('.blackout_container', this).length == 0) {
                var shadow = document.createElement('div');
                shadow.setAttribute('class', 'blackout_container');
                $(this).prepend(shadow);
            }
            $('.blackout_container', this).css("z-index", 12);
            $('.note', this).css("z-index", 12);
            if (!AuUtils.isIE()) {
                $('.blackout_container', this).finish();
                $('.blackout_container', this).animate({backgroundColor: '#322508', opacity: opacity}, animation_speed);
            } else {
                $('.blackout_container', this).css('filter', "progid:DXImageTransform.Microsoft.gradient( startColorstr='#1a322508', endColorstr='#1a322508',GradientType=0 )");
            }
        },
        function () {
            $('.blackout_container', this).css("z-index", 0);
            $('.note', this).css("z-index", 0);
            if (!AuUtils.isIE()) {
                $('.blackout_container', this).finish();
                $('.blackout_container', this).animate({
                    backgroundColor: '#322508',
                    opacity: no_opacity
                }, animation_speed);
            } else {
                $('.blackout_container', this).css('filter', "progid:DXImageTransform.Microsoft.gradient( startColorstr='#1affffff', endColorstr='#1affffff',GradientType=0 )");
            }
        }
    );
}

function initTreeLogic() {
    var animationSpeed = 200;
    var images_asc = ["images/arrow00.png", "images/arrow15.png", "images/arrow30.png", "images/arrow45.png", "images/arrow60.png", "images/arrow75.png", "images/arrow90.png"];

    var images_desc = images_asc.slice(0);
    images_desc.reverse();
    function openTree($node) {
        var $image = $('.tree_btn>img', $node);
        $('>ul', $node).slideDown(animationSpeed, function () {
            $('>ul', $node).removeClass('tree_node_close').addClass('tree_node_open');
        });
        if ($image.length) {
            rotateIcon($image, images_asc, animationSpeed / images_asc.length);
        }
    }

    function closeTree($node) {
        var $image = $('.tree_btn>img', $node);
        $('>ul', $node).slideUp(animationSpeed, function () {
            $('>ul', $node).removeClass('tree_node_open').addClass('tree_node_close');
        });
        if ($image.length) {
            rotateIcon($image, images_desc, animationSpeed / images_desc.length);
        }
    }

    function rotateIcon(imgObject, images, timeStep) {
        var imageIndex = 0;
        var animation = setInterval(function () {
            imageIndex++;
            imgObject[0].src = images[imageIndex];
            if (imageIndex == images.length - 1) {
                clearInterval(animation);
            }
        }, timeStep);
    }

    function initCloseNav() {
        $('.nav-close-btn').click(function () {
            $('.nav-tree-container').removeClass('opened');
        });
    }


    initCloseNav();

    $('.tree .tree_btn').click(function () {
        var $node = $(this).closest('li');

        if ($('>ul', $node).css('display') == 'none') {
            openTree($node);
        } else {
            closeTree($node);
            //close all inner nodes
            $nodesToClose = $('li', $node);
            for (var nodeIndex = 0; nodeIndex < $nodesToClose.length; nodeIndex++) {
                if ($('ul.tree_node_open', $nodesToClose[nodeIndex]).length) {
                    closeTree($($nodesToClose[nodeIndex]));
                }
            }
        }
    });
}

function initPathLinkSideBar() {
    var navBar = $('.nav-tree-container');
    $('[data-code=GN]').click(function (event) {
        /*        if ($(window).width() <= APP.SIDEBAR_WIN_WIDTH_POINT) {*/
        if (navBar.hasClass('opened')) {
            navBar.removeClass('opened');
        } else {
            navBar.addClass('opened');
        }
        /*        } else {
         navBar.removeClass('opened');
         window.location.href = $(this).data('href');
         }*/
    });
}

function initPathLinkViewMode() {
    $('.view_mode>.numeric li>div').mouseup(function () {
        var conf = {};
        conf[params.ITEMS_COUNT] = $(this).text();
        conf[params.PAGE_NUM] = 1;
        var url = AuUtils.getModifiedCurrentUrl(conf);
        window.location.href = url;
    });
    $('.view_mode>.view li>div').click(function () {
        var urlObj = AuUtils.getUrlAsObject(document.URL);
        var conf = {};
        conf[params.VIEW_MODE] = $(this).attr('view_type');
        conf[params.PAGE_NUM] = urlObj.params[params.PAGE_NUM] || 1;
        var url = AuUtils.getModifiedCurrentUrl(conf);
        window.location.href = url;
    });
}

function debounce(callback, timeout) {
    var debounceTimeout;
    return function () {
        var args = arguments;
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(function () {
            callback.call(null, args);
        }, timeout);
    };
}

function initPreviewImage() {
    if ($('.s_images img').length) {
        var $galleryViewport = $('.viewport');
        var $galleryViewportWidth = $galleryViewport.data('width');
        var $window = $(document);



        var imageGallery = new ImageGallery().init('.s_images>div', '.m_images img', '.big_img', '.viewport_images', '#gallery');
        imageGallery.current = 0;
        imageGallery.changeMainImage(imageGallery.current);
        var imageNamePrepare = function (inName, prefix) {
            var matches = /^(.*\/)+([^\/]+)$/g.exec(inName);
            var imagePath = matches[1];
            var imageName = /^.*(_.*)$/g.exec(matches[2])[1];
            return imagePath + prefix + imageName;
        };
        var zoomCallback = function (state) {
            if (state == 'zoom_in') {
                imageGallery.hideArrows();
            } else if (state == 'zoom_out') {
                imageGallery.showArrows();
            }
        };
        imageGallery.attachEvent('imageChange', function (oldImage, newImage) {
            imageZoom.updatePreviewImage(imageNamePrepare(newImage, 'm'));
            imageZoom.updateZoomedImage(imageNamePrepare(newImage, 'l'));
        });
        var imageZoom = new ImageZoom().init('.squareX', '#main_gallery_image', imageNamePrepare, zoomCallback);
    }
}

function initSearchLogic() {
    var searchHandle = function (valueToSearch) {
        if (typeof(valueToSearch) != 'undefined' && valueToSearch.trim().length > 0) {
            var urlObj = {
                page_name: 'search',
                search_value: encodeURIComponent(valueToSearch)
            };
            window.location.href = AuUtils.getModifiedCurrentUrl(urlObj);
        }
    };
    $('.search-button-desk').on('click', function () {
        var valueToSearch = $('.search_input').val();
        searchHandle(valueToSearch);
    });
    $('.search_input').on('keypress', function () {
        var valueToSearch = $('.search_input').val();
        if (event.which == 13) {
            searchHandle(valueToSearch);
        }
    });

    var searchValue = AuUtils.getParamFromCurrentUrl('search_value');
    if (pages.isSearch && typeof(searchValue) != 'undefined') {
        $('.search_input').val(decodeURIComponent(searchValue));
    }
}

function initPriceListLogic(callback) {
    var dataFormatter = function (data) {
        var resDOM = document.createElement('TABLE');
        resDOM.setAttribute('cellpadding', 0);
        resDOM.setAttribute('cellspacing', 0);
        resDOM.setAttribute('style', 'margin: 5px;')
        if (AuUtils.hasContent(data)) {
            for (var dataIndex = 0; dataIndex < data.length; dataIndex++) {
                var row = resDOM.insertRow(-1);
                row.setAttribute('class', 'download-item');

                var icon = row.insertCell(-1);
                var iconDiv = document.createElement('DIV');
                iconDiv.setAttribute('class', 'xls-icon');
                icon.appendChild(iconDiv);

                var text = row.insertCell(-1);
                var textDiv = document.createElement('DIV');
                textDiv.innerHTML = data[dataIndex].name;
                textDiv.setAttribute('class', 'download-item-text f-14');
                text.appendChild(textDiv);

                var downloadBtn = row.insertCell(-1);
                var downloadBtnDiv = document.createElement('A');
                downloadBtnDiv.setAttribute('class', 'f-13 download_btn button input_hover');
                downloadBtnDiv.setAttribute('href', data[dataIndex].path);
                downloadBtnDiv.innerHTML = 'скачать';
                downloadBtn.appendChild(downloadBtnDiv);
            }
        }
        return resDOM;
    };
    var hideCallback = function () {
        if ($(popup.containerSelector + ':hover').length == 0) {
            popup.$container.trigger('mouseleave');
        }
    };
    var popup = new Popup().init('#download>a', undefined, hideCallback, false);
    popup.loadDataWithAjax('getPrices', dataFormatter, callback);
    return popup;
}

function initTopBarScrolling() {
    var $topBottomMainMenu = $('.top_bottom_main_menu');
    var $topBar = $('.top_bar');
    var $topPanelFixed = $('#top_panel_fixed');
    var topBarFillingTheWidthAnimation = function (obj) {
        var height = $topPanelFixed.height() - $topBottomMainMenu.height();
        $(obj).css('position', 'fixed');
        $(obj).css('top', '-' + (height) + 'px');
    };
    var topBarСompressWidth = function (obj) {
        $(obj).css('position', 'relative');
        $(obj).css('top', '');
    };
    $(window).on('scroll', function () {
        if ($topBottomMainMenu.length && $topBar.length) {
            var height = $topPanelFixed.height() - $topBottomMainMenu.height();
            var yPosition = $topBottomMainMenu[0].getBoundingClientRect().top;
            var yContainerPosition = $topBar[0].getBoundingClientRect().bottom;
            var docTop = Math.abs(document.body.getBoundingClientRect().top);
            if (docTop >= height && yPosition <= 0) {
                topBarFillingTheWidthAnimation($topPanelFixed[0]);
            } else if (yContainerPosition >= height / 2) {
                topBarСompressWidth($topPanelFixed[0]);
            }
        }
    });
}

function initNewsGalleryLogic() {
    var $newsViewPort = $('.news_items');
    var $newsItemsFullSizeContainer = $('.news_items_container');
    var $newsItems = $('.news_item', $newsItemsFullSizeContainer);
    var $prevArrow = $('.gallery_left_arrow_bold');
    var $nextArrow = $('.gallery_right_arrow_bold');
    var currentPosition = 0;
    var widths = [];
    for (var itemIndex = 0; itemIndex < $newsItems.length; itemIndex++) {
        widths.push($($newsItems[itemIndex]).outerWidth(true));
    }
    select(0);
    function getPosition(index) {
        if (index <= 0) {
            return 0;
        }
        var position = 0;
        index--;
        for (var widthIndex = index; widthIndex >= 0; widthIndex--) {
            position += widths[widthIndex];
        }
        return position;
    };
    function select(position) {
        var currentNewsWidth = widths[position];
        var indent = ($newsViewPort.outerWidth() - currentNewsWidth) / 2;
        var left = getPosition(position) - indent;
        $newsItemsFullSizeContainer.finish();
        $newsItemsFullSizeContainer.animate({left: -left}, 500);
    }

    $nextArrow.click(function () {
        currentPosition = currentPosition >= $newsItems.length - 1 ? 0 : ++currentPosition;
        console.info(currentPosition);
        select(currentPosition);
    });
    $prevArrow.click(function () {
        currentPosition = currentPosition <= 0 ? $newsItems.length - 1 : --currentPosition;
        console.info(currentPosition);
        select(currentPosition);
    });
}

function initPriceGalleryLogic() {
    var selectedClass = 'selected_description';
    var $pricesGallery = $('.prices_gallery');
    var $pricesButtonsContainer = $('.prices_buttons', $pricesGallery);
    var $pricesDescriptions = $('.prices_description', $pricesGallery)
    var $priceButtons = $('.price_button', $pricesButtonsContainer);
    var sliderTimeout = undefined;
    var sliderInterval = undefined;
    var isSliderIntervalStoped = false;
    var initSlidingInterval = function () {
        isSliderIntervalStoped = false;
        sliderInterval = setInterval(
            function () {
                /*selectNext();*/
            },
            3000
        );
    };
    var selectDescription = function (newSelectedCode) {
        var $oldSelected = $pricesDescriptions.filter('.' + selectedClass);
        var oldSelectedCode = $oldSelected.attr('price_code');
        var $newSelected = $pricesDescriptions.filter('[price_code=' + newSelectedCode + ']');
        $oldSelected.finish();
        $oldSelected.removeClass(selectedClass);
        $oldSelected.fadeOut(600);

        var _$filtered = $priceButtons.filter('[price_code=' + oldSelectedCode + ']');
        _$filtered.removeClass('input_hover_untouchable');
        _$filtered.finish();
        /*inputHoverModule.inn().hoverOut(_$filtered[0]);*/

        $priceButtons.filter('[price_code=' + oldSelectedCode + ']')
        $newSelected.finish().addClass(selectedClass).fadeIn(600);
        _$filtered = $priceButtons.filter('[price_code=' + newSelectedCode + ']');
        /*inputHoverModule.inn().hoverIn(_$filtered[0]);*/
        _$filtered.addClass('input_hover_untouchable');
    };
    var selectNext = function () {
        var $oldSelected = $pricesDescriptions.filter('.' + selectedClass);
        var next = $oldSelected.next();
        if (!next.length) {
            next = $pricesDescriptions.get(0);
        }
        var code = $(next).attr('price_code');
        selectDescription(code);
        console.info('selectNext  > >');
    };

    var selectPrev = function () {
        var $oldSelected = $pricesDescriptions.filter('.' + selectedClass);
        var prev = $oldSelected.prev();
        if (!prev.length) {
            prev = $pricesDescriptions.last();
        }
        var code = $(prev).attr('price_code');
        selectDescription(code);
        console.info('selectPrev < <');
    };
    $pricesGallery.hover(
        function () {
            clearTimeout(sliderTimeout);
            clearInterval(sliderInterval);
            isSliderIntervalStoped = true;
            /*$pricesButtonsContainer.finish();
             $pricesButtonsContainer.fadeIn();*/
        },
        function () {
            /*sliderTimeout = setTimeout(function() {
             $pricesButtonsContainer.finish();
             $pricesButtonsContainer.fadeOut()
             }, 1500);*/
            if (isSliderIntervalStoped) {
                initSlidingInterval();
            }
        }
    )
    $priceButtons.click(function () {
        $pricesDescriptions;
        clearInterval(sliderInterval);
        isSliderIntervalStoped = true;
        var code = $(this).attr('price_code');
        selectDescription(code);
    });
    initSlidingInterval();
    $("body").keydown(function (e) {
        if (e.keyCode == 37) { // prev
            clearInterval(sliderInterval);
            selectPrev();
        } else if (e.keyCode == 39) { // next
            clearInterval(sliderInterval);
            selectNext();
        }
    });
}

function initCatalogItemButton() {
    $('.catalog_item_button_container').hover(
        function () {
            var $button = $('.catalog_item_button', this);
            $button.show();
        },
        function () {
            var $button = $('.catalog_item_button', this);
            $button.hide();
        }
    )
}

function initEntryLogic() {
    var $template = "\
        <div class='admin_auth_container' >\
            <label class='f-15' for='user'>Пользователь</label>\
            <input id='user'>\
            <label class='f-15' for='password'>Пароль</label>\
            <input id='password' type='password'>\
            <button class='button f-15 input_hover'>Войти</button>\
        </div>";
    var popup = new Popup().init('#entry', null, null, false);
    popup.setData($template);
    $('#entry button').focusin(function () {
        $(this).trigger('mouseenter');
    }).focusout(function () {
        $(this).trigger('mouseleave');
    });
}

$(document).ready(function () {
    new SearchInput().initialize(
        '.search_input',
        '.search-result-placeholder',
        '.search-button-mob',
        '.search_button_container',
        '.search_input-close'
    );
    keyBoard.init();
    pages = {};
    var currentPageName = AuUtils.getParamFromCurrentUrl(params.PAGE_NAME);
    for (var key in params) {
        if (key.indexOf('PAGE__') == 0) {
            var pageName = params[key];
            pages['is' + AuUtils.makeFirstCapitalLetter(pageName)] = currentPageName == params[key];
        }
    }
    if (!currentPageName || currentPageName.trim() == '') {
        pages.isMain = true;
    }
    initEntryLogic();
    /*initPriceListLogic(inputHoverModule.update);*/
    initTopBarScrolling(93);
    initSearchLogic();
    /*inputHoverModule.update();*/
    initPathLinkSideBar();
    initPathLinkViewMode();
    //initCatalogItemButton();
    if (pages.isMain) {
        initPriceGalleryLogic();
        //initNewsGalleryLogic();
    } else if (pages.isCatalog) {
        initTreeLogic();
        keyBoard.handle(37, function () {
            $('.prev_link').trigger('click')
        });
        keyBoard.handle(39, function () {
            $('.next_link').trigger('click')
        });

    } else if (pages.isContacts) {
        googleMapLogic.init(/*inputHoverModule.update*/);

    } else if (pages.isSingleItem) {
        initPreviewImage();
        initTreeLogic();
    }

    var agentClass = AuUtils.isIE() ? 'ie' : 'not_ie';
    if (AuUtils.isOldIe() || AuUtils.isIE9()) {
        agentClass += 'old_ie';
    }
    document.body.className += ' ' + agentClass;

    (new Feedback()).initialize();

    $.fn.centered_popup = function () {
        this.css('position', 'fixed');
        this.css('top', (document.body.clientHeight - this.height()) / 2);
        this.css('left', (document.body.clientWidth - this.width()) / 2);
        console.log("window.height: " + $(window).height());
        console.log("window.width: " + $(window).width());
        console.log("this.height: " + this.height());
        console.log("this.width: " + this.width());
        console.log("height: " + document.body.clientHeight);
        console.log("width: " + document.body.clientWidth);
    };
});
/*------------------------------------------gallery---------------------------------------*/

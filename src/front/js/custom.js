var APP = {
    GOOGLE_MAPS_API_KEY: 'AIzaSyDzIFbxpxA5fnsZ4EPQGYHNzycoi2GdU1U',
    COLOR_ACTIVE: '#808080',
    COLOR_UN_ACTIVE: '#88cc55',
    COLOR_MOUSE_DOWN: '#737373',
    SIDEBAR_WIN_WIDTH_POINT: '1280'
};

if (typeof(console) == 'undefined') {
    var console = (function() {
        return {
            info: function(){},
            log: function(){},
            warn: function(){}
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
	ITEMS_COUNT: 'items_count',
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

function buildUrl() {
    var url = new String("?");
    for (index = 0; index < keys_GET.length; index++) {
        url += keys_GET[index] + "=" + values_GET[index] + "&";
    }
    console.info('URL: ' + url + '   KEYS: ' + keys_GET.length);
    return keys_GET.length > 0 ? url.slice(0, url.length - 1) : '/';
}

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

function getValueByParametreFromURL(URL, parameter) {
    var pattern = parameter + "=[\\w%]*";
    var reg = new RegExp(pattern);
    var res = reg.exec(URL);
    if (res == null) {
        return '';
    } else {
        return res[0].substring(res[0].indexOf('=') + 1);
    }
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

function pause(ms) {
    var date = new Date();
    var curDate = null;
    do {
        curDate = new Date();
    }
    while (curDate - date < ms);
}



function hasClass(elem, className) {
    return new RegExp("(^|\\s)" + className + "(\\s|$)").test(elem.className)
}

function closeTree(liObject) {
    var liLlst = liObject.getElementsByTagName('li');
    if (liLlst.length != 0) {
        $(liObject.childNodes[1]).css({"background-image": arrows[0]});
    }
}


function buildUrl(urlObj) {
    var url = ''
    if (typeof(urlObj) != 'undefined') {
        for (var key in urlObj) {
            url += key + '=' + urlObj[key] + '&';
        }
    }
    return url;
}



/*------------------------------------------gallery end---------------------------------------*/


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
            if (!U.isIE()) {
                $('.blackout_container', this).finish();
                $('.blackout_container', this).animate({backgroundColor: '#322508', opacity: opacity}, animation_speed);
            } else {
                $('.blackout_container', this).css('filter', "progid:DXImageTransform.Microsoft.gradient( startColorstr='#1a322508', endColorstr='#1a322508',GradientType=0 )");
            }
        },
        function () {
            $('.blackout_container', this).css("z-index", 0);
            $('.note', this).css("z-index", 0);
            if (!U.isIE()) {
                $('.blackout_container', this).finish();
                $('.blackout_container', this).animate({backgroundColor: '#322508', opacity: no_opacity}, animation_speed);
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
        $('>ul', $node).slideDown(animationSpeed, function() {
            $('>ul', $node).removeClass('tree_node_close').addClass('tree_node_open');
        });
        if ($image.length) {
            rotateIcon($image, images_asc, animationSpeed / images_asc.length);
        }
    }

    function closeTree($node) {
        var $image = $('.tree_btn>img', $node);
        $('>ul', $node).slideUp(animationSpeed, function() {
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
        $('.nav-close-btn').click(function() {
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
    $('[data-code=GN]').click(function(event) {
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
    $('.view_mode>.numeric li>div').mouseup(function() {
        var conf = {};
        conf[params.ITEMS_COUNT] = $(this).text();
        conf[params.PAGE_NUM] = 1;
        var url = U.getModifiedCurrentUrl(conf);
        window.location.href = url;
    });
    $('.view_mode>.view li>div').click(function() {
        var urlObj = U.getUrlAsObject(document.URL);
        var conf = {};
        conf[params.VIEW_MODE] = $(this).attr('view_type');
        conf[params.PAGE_NUM] = urlObj.params[params.PAGE_NUM] || 1;
        var url = U.getModifiedCurrentUrl(conf);
        window.location.href = url;
    });
}

function initPreviewImage() {
    if ($('.s_images img').length) {
        var imageGallery = new ImageGallery().init('.s_images>div', '.m_images img', '.big_img', '.viewport_images', '#gallery');
        imageGallery.current = 0;
        imageGallery.changeMainImage(imageGallery.current);
        var imageNamePrepare = function(inName, prefix) {
            var matches = /^(.*\/)+([^\/]+)$/g.exec(inName);
            var imagePath = matches[1];
            var imageName = /^.*(_.*)$/g.exec(matches[2])[1];
            return imagePath + prefix + imageName;
        };
        var zoomCallback = function(state) {
            if (state == 'zoom_in') {
                imageGallery.hideArrows();
            } else if (state == 'zoom_out') {
                imageGallery.showArrows();
            }
        };
        imageGallery.attachEvent('imageChange', function(oldImage, newImage) {
            imageZoom.updatePreviewImage(imageNamePrepare(newImage, 'm'));
            imageZoom.updateZoomedImage(imageNamePrepare(newImage, 'l'));
        });
        var imageZoom = new ImageZoom().init('.squareX', '#main_gallery_image', imageNamePrepare, zoomCallback);
    }
}

function initSearchLogic() {
    var searchHandle = function(valueToSearch) {
        if (typeof(valueToSearch) != 'undefined' && valueToSearch.trim().length > 0) {
            var urlObj = {
                page_name: 'search',
                search_value: encodeURIComponent(valueToSearch)
            };
            window.location.href = U.getModifiedCurrentUrl(urlObj);
        }
    };
    $('.search_button').on('click', function() {
        var valueToSearch = $('.search_input').val();
        searchHandle(valueToSearch);
    });
    $('.search_input').on('keypress', function() {
        var valueToSearch = $('.search_input').val();
        if (event.which == 13) {
            searchHandle(valueToSearch);
        }
    });

    var searchValue = U.getParamFromCurrentUrl('search_value');
    if (pages.isSearch && typeof(searchValue) != 'undefined') {
        $('.search_input').val(decodeURIComponent(searchValue));
    }
}

function initPriceListLogic(callback) {
    var dataFormatter = function(data) {
        var resDOM = document.createElement('TABLE');
        resDOM.setAttribute('cellpadding', 0);
        resDOM.setAttribute('cellspacing', 0);
        resDOM.setAttribute('style', 'margin: 5px;')
        if (U.hasContent(data)) {
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
    var hideCallback = function() {
        if ($(popup.containerSelector + ':hover').length == 0) {
            popup.$container.trigger('mouseleave');
        }
    };
    var popup = new Popup().init('#download>a', undefined, hideCallback, false);
    popup.loadDataWithAjax('getPrices', dataFormatter, callback);
    return popup;
}

function initFeedbackLogic() {
    var popupContentTemplate = "\
                <div id='feedback' class='bottom_panel_item w-33p'>\
                    <div class='feedback_blocker'></div>\
                    <div class='feedback_title f-16'>Напишите нам письмо</div>\
                    <div class='feedback_container'>\
                        <div class='input_block' >\
                            {message_label}\
                            <textarea id='message' {message_placeholder} class='message input f-15' cols='40' rows='5'></textarea>\
                            <div class='message_validation f-11'></div>\
                        </div>\
                        <div class='input_block' >\
                            {name_label}\
                            <input id='name' {name_placeholder} class='name input f-15'>\
                            <div class='name_validation f-11'></div>\
                        </div>\
                        <div class='input_block'>\
                            {email_label}\
                            <input id='email' {email_placeholder} class='email input f-15'>\
                            <div class='email_validation f-11'></div>\
                        </div>\
                        <button class='send input_hover input_block f-17 button'>Отправить</button>\
                    </div>\
                </div>\
        ";
    if (U.isOldIe()) {
        popupContentTemplate = templates.replace(popupContentTemplate, {
            message_label: "<label for='message' class='f-15'>Содержимое письма</label>",
            name_label:    "<label for='name' class='f-15'>Меня зовут</label>",
            email_label:   "<label for='email' class='f-15'>Мой e-mail</label>",
            message_placeholder: "",
            name_placeholder: "",
            email_placeholder: ""
        })
    } else {
        popupContentTemplate = templates.replace(popupContentTemplate, {
            message_label: "",
            name_label:    "",
            email_label:   "",
            message_placeholder: "placeholder='Содержимое письма'",
            name_placeholder: "placeholder='Меня зовут'",
            email_placeholder: "placeholder='Мой e-mail'"
        })
    }
    $('.bottom_panel_window').prepend(popupContentTemplate);
    var $feedback = $('#feedback');
    var $feedbackBlocker = $('.feedback_blocker' , $feedback);
    var $feedbackSendBtn = $('.send' , $feedback);
    //popup.setData(popupContent);
    isFeedbackNotActive = function() {
        return !popup.isVisible;
    };

    var $feedback = $('#feedback');
    var isFormSubmit = false;
    var initFormValidation = function() {
        $('.message', $feedback).on('change paste focus textInput input', function() {
            if (isFormSubmit) {
                var value = $(this).val();
                if (U.hasContent(value) && (value.length > 1000 || value.length == 0)) {
                    $('.message', $feedback).addClass('validation_error');
                    $('.message_validation', $feedback).addClass('validation_message').val('Сообщение слишком большое.');
                } else {
                    $('.message', $feedback).removeClass('validation_error');
                    $('.message_validation', $feedback).removeClass('validation_message').val('');
                }
                console.info('#feedback .message');
            }
        });
        $('.name', $feedback).on('change paste focus textInput input', function() {
            if (isFormSubmit) {
                var value = $(this).val();
                if (U.hasContent(value) && value.length > 50) {
                    $('.name', $feedback).addClass('validation_error');
                    $('.name_validation', $feedback).addClass('validation_message').val('Имя слишком большое.');
                } else {
                    $('.name', $feedback).removeClass('validation_error');
                    $('.name_validation', $feedback).removeClass('validation_message').val('');
                }
                console.info('#feedback .name');
            }
        });
/*        $('.email', $feedback).on('change paste focus textInput input', function() {
            if (isFormSubmit) {
                var value = $(this).val();
                var regexp = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                var result = regexp.test(value);
                if (!result) {
                    $('.email', $feedback).addClass('validation_error');
                    $('.email_validation', $feedback).addClass('validation_message').val('Email некорректный.');
                } else {
                    $('.email', $feedback).removeClass('validation_error');
                    $('.email_validation', $feedback).removeClass('validation_message').val('');
                }
                console.info('#feedback .email');
            }
        });*/
    };
    var validateForm = function() {
        $('.input', $feedback).trigger('change');
        return $('.validation_message', $feedback).length == 0;
    };

    var isEmptyMessage = function() {
        return $('.message', $feedback).val() == '';
    };
    initFormValidation();
    $feedbackSendBtn.on('click', function() {
        isFormSubmit = true;
        if (!$(this).hasClass('input_disable') && validateForm() && !isEmptyMessage()) {
            var messageBody = $('.message', $feedback).val();
            var senderName = $('.name', $feedback).val();
            var senderEmail = $('.email', $feedback).val();
            var data = {
                message_body: messageBody,
                sender_name: senderName,
                sender_email: senderEmail
            }

            $feedbackBlocker.fadeIn(100);
            $feedbackBlocker.removeClass('successful').removeClass('error').addClass('progress');
            $feedbackSendBtn.addClass('input_disable')
            $.ajax({
                type: "POST",
                dataType: 'json',
                data: data,
                url: '/api/sendFeedbackEmail',
                context: document.body
            }).done(function(data) {
                    $feedbackSendBtn.removeClass('input_disable').trigger('mouseleave');
                    if (data === true) {
                        $feedbackBlocker.removeClass('progress').removeClass('error').addClass('successful');
                    } else {
                        $feedbackBlocker.removeClass('progress').removeClass('successful').addClass('error');
                    }
                    setTimeout(function() {
                        if (data === true) {
                            isFormSubmit = false;
                            $('input, textarea', $feedback).val('');
                            $('.validation_error', $feedback).removeClass('validation_error');
                        }
                        $feedbackBlocker.fadeOut(100);
                    }, 3000);
                }
            );
        }
    }).focusin(function() {
        $(this).trigger('mouseenter');
    }).focusout(function() {
        $(this).trigger('mouseleave');
    });
}

var googleMapLogic = (function() {
    var data_ = undefined;
    var markers = [];
    var infoWindows = [];
    var init_ = function(callback) {
        if (pages.isContacts) {
            var script = document.createElement("script");
            script.type = "text/javascript";
            script.src = "http://maps.googleapis.com/maps/api/js?key=" + APP.GOOGLE_MAPS_API_KEY + "&sensor=true&callback=googleMapLogic.postLoader";
            document.body.appendChild(script);

            $.ajax({
                dataType: 'json',
                url: "/api/getAddresses",
                context: document.body
            }).done(function(data) {
                    data_ = data;
                    markers = [];
                    if (typeof(updateView) != 'undefined') {
                        updateView(data_, callback);
                    }
            });
        }
    };

    var updateView = function(data, callback) {
        if (U.hasContent(data) && data.length) {
            var container = document.getElementById('contact_list');
            for (var dataIndex = 0; dataIndex < data.length; dataIndex++) {
                var item = data[dataIndex];
                var contact = document.createElement('DIV');
                contact.setAttribute('class', 'contact_item f-15');
                contact.setAttribute('data_id', dataIndex);

                var title = document.createElement('DIV');
                title.setAttribute('class', 'title f-18');
                title.innerHTML = item.description;

                var numbers = document.createElement('DIV');
                numbers.setAttribute('class', 'contact_numbers');
                var numberCount = 0;
                if (U.hasContent(item.mobileNumbers)) {
                    for (var key in item.mobileNumbers) {
                        if (U.hasContent(key) && U.hasContent(item.mobileNumbers[key]) && key.trim() != '' && item.mobileNumbers[key].trim() != '') {
                            numberCount++;
                            var numberItem = document.createElement('DIV');
                            var icon = document.createElement('DIV');
                            icon.setAttribute('class', key + '_number');
                            var numbersContainer = document.createElement("DIV")
                            var numbersList = item.mobileNumbers[key].split(",");
                            numbersContainer.innerHTML = numbersList.join('<br>');
                            numberItem.appendChild(icon);
                            numberItem.appendChild(numbersContainer);
                            numbers.appendChild(numberItem);
                        }
                    }
                }
                var prices = document.createElement('DIV');
                var priceCount = 0;
                prices.setAttribute('class', 'contact_prices');
                if (U.hasContent(item.prices)) {
                    var pricesList = document.createElement('UL')
                    prices.appendChild(pricesList);
                    for (var priceIndex = 0; priceIndex < item.prices.length; priceIndex++) {
                        if (U.hasContent(item.prices[priceIndex]) && item.prices[priceIndex].trim() != '') {
                            priceCount++;
                            var price = document.createElement('LI');
                            price.innerHTML = item.prices[priceIndex];
                            pricesList.appendChild(price);
                        }
                    }

                }

                contact.appendChild(title);
                if (numberCount > 0) {
                    contact.appendChild(numbers);
                }
                if (priceCount > 0) {
                    contact.appendChild(prices);
                }

                var viewInMap = document.createElement('DIV');
                viewInMap.setAttribute('class', 'button view_in_map_button input_hover f-15');
                if (U.hasContent(item.map)) {
                    var title = document.createElement('DIV');
                    title.innerHTML = 'показать на карте';
                    viewInMap.appendChild(title);
                    contact.appendChild(viewInMap);
                }

                U.appendShadow(contact);
                container.appendChild(contact);
            }
            initEvents.call(googleMapLogic);
        }
        if (typeof(callback) == 'function') {
            callback();
        }
    };

    var initGoogleMap = function() {
        var mapOptions = {
            center: new google.maps.LatLng(53.621351, 23.867684),
            zoom: 14,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        return map = new google.maps.Map(document.getElementById("google_map"), mapOptions);
    };

    var initEvents = function() {
        $('.view_in_map_button').on('mouseup', function() {
            var $contactItem = $(this).parent('[data_id]');
            var dataIndex = $contactItem.attr('data_id');
            var mapCoordinates = data_[dataIndex].map.split(',');
            if (mapCoordinates.length == 2) {
                removeMarkers();
                googleMapLogic.googleMap.setZoom(16);
                var marker = new google.maps.Marker({
                    position: new google.maps.LatLng(mapCoordinates[0], mapCoordinates[1]),
                    map: googleMapLogic.googleMap,
                    title: data_[dataIndex].description,
                    animation: google.maps.Animation.DROP
                });
                google.maps.event.addListener(marker, 'click', function() {
                    var latLng = this.getPosition(); // returns LatLng object
                    googleMapLogic.googleMap.setCenter(latLng);
                    googleMapLogic.googleMap.setZoom(16);
                });
                var infoWindowCaller = setInterval(function() {
                    var infowindow = new google.maps.InfoWindow({
                        content: marker.title,
                        maxWidth: 200
                    });
                    infowindow.open(map, marker);
                    infoWindows.push(infowindow);
                    clearInterval(infoWindowCaller);
                }, 500);
                var latLng = marker.getPosition(); // returns LatLng object
                googleMapLogic.googleMap.setCenter(latLng);
                markers.push(marker);
            }
        });
    };

    var removeMarkers = function() {
        for (var markerIndex = 0; markerIndex < markers.length; markerIndex++) {
            markers[markerIndex].setMap(null);
            infoWindows[markerIndex].close();
        }
        markers.splice(0, markers.length)
    };

    return {
        init: function(callback) {
            init_(callback);
        },
        postLoader: function() {
            this.googleMap = initGoogleMap();
        }
    }
})();

function initTopBarScrolling(height) {
    var $topBottomMainMenu = $('.top_bottom_main_menu');
    var $topBar = $('.top_bar');
    var $topPanelFixed = $('#top_panel_fixed');
    var topBarFillingTheWidthAnimation = function(obj) {
        $(obj).css('position', 'fixed');
        $(obj).css('top', '-' + (height + 17)+ 'px');
    };
    var topBarСompressWidth = function(obj) {
        $(obj).css('position', 'relative');
        $(obj).css('top', '');
    };
    $(window).on('scroll', function() {
        if ($topBottomMainMenu.length && $topBar.length) {
            var yPosition = $topBottomMainMenu[0].getBoundingClientRect().top;
            var yContainerPosition = $topBar[0].getBoundingClientRect().bottom;
            if (yContainerPosition <= height/2 && yPosition <= 0) {
                topBarFillingTheWidthAnimation($topPanelFixed[0]);
            } else if (yContainerPosition >= height/2) {
                topBarСompressWidth($topPanelFixed[0]);
            }
        }
    });
};

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
    $nextArrow.click(function() {
        currentPosition = currentPosition >= $newsItems.length - 1? 0: ++currentPosition;
        console.info(currentPosition);
        select(currentPosition);
    });
    $prevArrow.click(function() {
        currentPosition = currentPosition <= 0? $newsItems.length - 1: --currentPosition;
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
    var initSlidingInterval = function() {
        isSliderIntervalStoped = false;
        sliderInterval = setInterval(
            function() {
                /*selectNext();*/
            },
            3000
        );
    };
    var selectDescription = function(newSelectedCode) {
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
    var selectNext = function() {
        var $oldSelected = $pricesDescriptions.filter('.' + selectedClass);
        var next = $oldSelected.next();
        if (!next.length) {
            next = $pricesDescriptions.get(0);
        }
        var code = $(next).attr('price_code');
        selectDescription(code);
        console.info('selectNext  > >');
    };

    var selectPrev = function() {
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
        function() {
            clearTimeout(sliderTimeout);
            clearInterval(sliderInterval);
            isSliderIntervalStoped = true;
            /*$pricesButtonsContainer.finish();
            $pricesButtonsContainer.fadeIn();*/
        },
        function() {
            /*sliderTimeout = setTimeout(function() {
                $pricesButtonsContainer.finish();
                $pricesButtonsContainer.fadeOut()
            }, 1500);*/
            if (isSliderIntervalStoped) {
                initSlidingInterval();
            }
        }
    )
    $priceButtons.click(function() {
        $pricesDescriptions;
        clearInterval(sliderInterval);
        isSliderIntervalStoped = true;
        var code = $(this).attr('price_code');
        selectDescription(code);
    });
    initSlidingInterval();
    $("body").keydown(function(e) {
        if(e.keyCode == 37) { // prev
            clearInterval(sliderInterval);
            selectPrev();
        } else if(e.keyCode == 39) { // next
            clearInterval(sliderInterval);
            selectNext();
        }
    });
}

function initCatalogItemButton() {
    $('.catalog_item_button_container').hover(
        function() {
            var $button = $('.catalog_item_button', this);
            var $imageContainer = $('.ciic img', this);
            /*$item.finish();*/
/*            $item.stop(true, false);*/
            /*$imageContainer.animo('blur');*/
            $button.show();
        },
        function() {
            var $button = $('.catalog_item_button', this);
            var $imageContainer = $('.ciic img', this);
            /*$imageContainer.animo('focus');*/
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
    $('#entry button').focusin(function() {
        $(this).trigger('mouseenter');
    }).focusout(function() {
        $(this).trigger('mouseleave');
    });
}

$(document).ready(function () {
    keyBoard.init();
    pages = {};
    var currentPageName = U.getParamFromCurrentUrl(params.PAGE_NAME);
    for (var key in params) {
        if (key.indexOf('PAGE__') == 0) {
            var pageName = params[key];
            pages['is' + U.makeFirstCapitalLetter(pageName)] = currentPageName == params[key];
        }
    }
    if (!currentPageName || currentPageName.trim() == '') {
        pages.isMain = true;
    }


    initEntryLogic();
    /*initPriceListLogic(inputHoverModule.update);*/
    initTopBarScrolling(93);
    initFeedbackLogic();
    initSearchLogic();
    /*inputHoverModule.update();*/
    initPathLinkSideBar();
    initPathLinkViewMode();
    initCatalogItemButton();
    if (pages.isMain) {
        initPriceGalleryLogic();
        //initNewsGalleryLogic();
    } else if (pages.isCatalog) {
        initTreeLogic();
        keyBoard.handle(37, function() {$('.prev_link').trigger('click')});
        keyBoard.handle(39, function() {$('.next_link').trigger('click')});

    } else if (pages.isContacts) {
        googleMapLogic.init(/*inputHoverModule.update*/);

    } else if (pages.isSingleItem) {
        new TransparentIcons().init();
        initPreviewImage();
        initTreeLogic();
    }

    var blackouts = $('.blackout');
    if (blackouts.length > 0) {
        var shadow = document.createElement('div');
        shadow.setAttribute('class', 'blackout_container');
        $(blackouts).prepend(shadow);
    }
    var agentClass = U.isIE()? 'ie': 'not_ie';
    $('body').addClass(agentClass);
});
    /*------------------------------------------gallery---------------------------------------*/

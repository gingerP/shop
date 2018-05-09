(function () {
    var searchResultTemplate = [
        '		<div class="search-result-container">',
        '           <div class="search-result-scrolling">',
        '			{{#isEmpty}}',
        '				<div class="search-result-empty">',
        '                   К сожаление, ничего не найдено.',
        '                   <a href="">Очистить поиск</a>',
        '               </div>',
        '			{{/isEmpty}}',
        '			{{#contacts.0}}',
        '				<div class="search-result-contacts">',
        '					<div class="search-result-title">Контакты (найдено {{contacts.length}})</div>',
        '					{{#contacts}}',
        '						<a class="search-result-contact" href="{{url}}">{{name}}</a>',
        '					{{/contacts}}',
        '				</div>',
        '			{{/contacts.0}}',
        '			{{#navs.0}}',
        '				<div class="search-result-navs">',
        '					<div class="search-result-title">Категории (найдено {{navs.length}})</div>',
        '					{{#navs}}',
        '						<a class="search-result-nav" href="{{url}}">{{name}}</a>',
        '					{{/navs}}',
        '				</div>',
        '			{{/navs.0}}',
        '			{{#products.0}}',
        '				<div class="search-result-products">',
        '					<div class="search-result-title">Товары (найдено {{productsTotalCount}})</div>',
        '					{{#products}}',
        '						<a class="search-result-product" href="{{url}}">',
        '							<img class="search-result-product-icon" src="{{icon}}">',
        '							<div class="search-result-product-text">{{name}}</div>',
        '						</a>',
        '					{{/products}}',
        '				</div>',
        '			{{/products.0}}',
        '           </div>',
        '		</div>'
    ].join('');
    var productsTemplate = [
        '					{{#products}}',
        '						<a class="search-result-product" href="{{url}}">',
        '							<img class="search-result-product-icon" src="{{icon}}">',
        '							<div class="search-result-product-text">{{name}}</div>',
        '						</a>',
        '					{{/products}}'
    ].join('');

    function debounce(cb, time) {
        var timeout;
        return function () {
            var args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function () {
                cb.call(null, args);
            }, time);
        };
    }

    function SearchInput() {
    }

    SearchInput.prototype.initialize = function initialize() {
        var self = this;
        self._topPanelFooterSelector = '.top-panel-footer';
        self._panelSearchSelector = '.top-panel-search';
        self._selector = '.search-input';
        self._placeholderSelector = '.top-panel-search-result-placeholder';
        self._selectorMobButtonSelector = '.search-button-mob';
        self._parentSelector = '.search-button-container';
        self._searchBtnOpenerSelector = '.search-button-opener';
        self._searchBtnCloserSelector = '.search-button-closer';

        self._debounceTimeout = 300;
        self._blackoutMaxScreenSize = 700;

        self.$body = $(document.body);
        self.$mobButton = $(self._selectorMobButtonSelector);
        self.$input = $(self._selector);
        self.$placeholder = $(self._placeholderSelector);
        self.$parent = $(self._parentSelector);
        self.$searchBtnOpener = $(self._searchBtnOpenerSelector);
        self.$searchBtnCloser = $(self._searchBtnCloserSelector);
        self.$panelSearch = $(self._panelSearchSelector);
        self.$topPanelFooter = $(self._topPanelFooterSelector);

        self.initializeEvents();
        self.requestPage = 0;
        self.requestLimit = 10;
        self.products = [];
        self.navKeys = [];
        self.contacts = [];
        self.isMobOpened = false;
    };

    SearchInput.prototype.initializeEvents = function initializeEvents() {
        var self = this;
        self.$input
            .on('input', debounce(function () {
                if (self.hasChanged()) {
                    self.search();
                }
            }, self._debounceTimeout))
            .on('focus', function () {
                self.scrollToInput();
                self.search();
                self.activateBlackout();
            })
            .on('blur', debounce(function () {
                self.closeSearchView();
            }, self._debounceTimeout));
        self.$mobButton
            .on('click', function () {
                self.$parent.addClass('input-opened');
                self.$input.focus();
            });
        self.$searchBtnOpener
            .on('click', function () {
                self.isMobOpened = !self.isMobOpened;
                if (self.isMobOpened) {
                    setTimeout(function () {
                        self.$input.focus();
                    }, 300);
                    self.$body.addClass('block-scrolling');
                    self.$topPanelFooter.addClass('opened-mob-search');
                } else {
                    self.$body.removeClass('block-scrolling');
                    self.$topPanelFooter.removeClass('opened-mob-search');
                }
            });
        self.$searchBtnCloser
            .on('click', function () {
                if (self.isMobOpened) {
                    self.isMobOpened = false;
                    self.$body.removeClass('block-scrolling');
                    self.$topPanelFooter.removeClass('opened-mob-search');
                    self.closeSearchView();
                    self.clearSearchedData();
                }
            });
    };

    SearchInput.prototype.search = function search() {
        var self = this;
        self.value = self.$input.val().trim();
        if (!self.value) {
            self.hideResults();
        } else {
            self.requestPage = 0;
            self.requestSearch(self.value, function (data) {
                self.products = data.products || [];
                self.navKeys = data.navKeys || [];
                self.contacts = data.contacts || [];
                self.productsTotalCount = data.productsTotalCount;
                self.applySearchResults(true);
            });
        }
    };

    SearchInput.prototype.hasChanged = function hasChanged() {
        var self = this;
        return self.value !== self.$input.val();
    };

    SearchInput.prototype.applySearchResults = function () {
        var self = this;
        self.$placeholder.html(
            Mustache.render(searchResultTemplate, {
                isEmpty: !self.products.length && !self.navKeys.length && !self.contacts.length,
                products: self.products,
                navs: self.navKeys,
                contacts: self.contacts,
                productsTotalCount: self.productsTotalCount
            })
        );
        var $scroller = self.$placeholder.find('.search-result-container');
        var $scrolling = $scroller.find('.search-result-scrolling');
        $scroller.scroll(function () {
            if ($scroller.height() + $scroller.scrollTop() > $scrolling.height() - 100) {
                self.loadNext();
            }
        });
    };

    SearchInput.prototype.requestSearch = function requestSearch(searchValue, cb) {
        var self = this;
        $.ajax(
            '/api/search',
            {
                type: 'GET',
                data: {
                    search: searchValue,
                    page: self.requestPage,
                    limit: self.requestLimit
                },
                dataType: 'json',
                success: cb
            }
        );
    };

    SearchInput.prototype.loadNext = function loadNext() {
        var self = this;
        if (!self.loadingNextInProgress) {
            self.requestPage++;
            self.loadingNextInProgress = true;
            self.requestSearch(self.value, function (response) {
                var products = response.products;
                var htmlText = Mustache.render(productsTemplate, {products: products});
                $('.search-result-products').get(0).innerHTML += htmlText;
                self.products = self.products.concat(products);
                self.loadingNextInProgress = false;
            });
        }
    };

    SearchInput.prototype.hideResults = function hideResults() {
        var self = this;
        self.$placeholder.html('');
    };

    SearchInput.prototype.activateBlackout = function activateBlackout() {
        $(document.body).addClass('search-input-visible');
    };

    SearchInput.prototype.deactivateBlackout = function activateBlackout() {
        $(document.body).removeClass('search-input-visible');
    };

    SearchInput.prototype.scrollToInput = function scrollToInput() {
        var self = this;
        var width = $(window).width();
        if (width <= self._blackoutMaxScreenSize) {
            $(document).scrollTop(110);
        }
    };

    SearchInput.prototype.clearSearchedData = function clearSearchedData() {
        var self = this;
        self.closed = true;
        self.$input.val('');
        self.products = [];
        self.navKeys = [];
        self.contacts = [];
        self.requestPage = 0;
        return this;
    };

    SearchInput.prototype.closeSearchView = function closeSearchView() {
        var self = this;
        self.hideResults(false);
        self.$parent.removeClass('input-opened');
        self.deactivateBlackout();
    };

    new SearchInput().initialize();

})();

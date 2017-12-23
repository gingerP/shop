(function () {
    function debounce(cb, time) {
        var timeout;
        return function() {
            var args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                cb.call(null, args);
            }, time);
        }
    }
    Handlebars.registerPartial('productsPartial',
        '<div class="search-result-products">\
            <div class="search-result-title">Товары {{items.length}}</div>\
        {{#each items}}\
            <a class="search-result-product" href="{{this.url}}">\
                <img class="search-result-product-icon" src="{{this.icon}}">\
                <div class="search-result-product-text">{{this.name}}</div>\
            </a>\
        {{/each}}\
        </div>');
    Handlebars.registerPartial('contactsPartial',
        '<div class="search-result-contacts">\
            <div class="search-result-title">Контакты {{items.length}}</div>\
        {{#each items}}\
            <a class="search-result-contact" href="{{this.url}}">{{this.name}}</a>\
        {{/each}}\
        </div>');
    Handlebars.registerPartial('navKeysPartial',
        '<div class="search-result-navs">\
            <div class="search-result-title">Категории {{items.length}}</div>\
        {{#each items}}\
            <a class="search-result-nav" href="{{this.url}}">{{this.name}}</a>\
        {{/each}}\
        </div>');
    Handlebars.registerPartial('containerPartial',
        '<div class="search-result-container">\
            <div class="search-result-container-height">\
            {{#if isEmpty}}\
            <div class="search-result-empty">К сожаление, ничего не найдено</div>\
            {{/if}}\
            {{#if contacts.length}}\
            {{> contactsPartial items=contacts}}\
            {{/if}}\
            {{#if navKeys.length}}\
            {{> navKeysPartial items=navKeys}}\
            {{/if}}\
            {{#if products.length}}\
            {{> productsPartial items=products}}\
            {{/if}}\
            </div>\
        </div>');
    var container = Handlebars.compile('{{> containerPartial}}');

    SearchInput = function SearchInput() {};

    SearchInput.prototype.initialize = function initialize(inputSelector, resultPlaceholderSelector, selectorMobButtonSelector, parentSelector,  closeBtnSelector) {
        var self = this;
        self._selector = inputSelector;
        self._placeholderSelector = resultPlaceholderSelector;
        self._selectorMobButtonSelector = selectorMobButtonSelector;
        self._parentSelector = parentSelector;
        self._closeBtnSelector = closeBtnSelector;
        self._debounceTimeout = 300;
        self._blackoutMaxScreenSize = 700;
        self.$mobButton = $(self._selectorMobButtonSelector);
        self.$input = $(self._selector);
        self.$placeholder = $(self._placeholderSelector);
        self.$parent = $(self._parentSelector);
        self.$closeBtn = $(self._closeBtnSelector);
        self.initializeEvents();
        self.requestPage = 0;
        self.requestLimit = 200;
        self.products = [];
        self.navKeys = [];
        self.contacts = [];
    };

    SearchInput.prototype.initializeEvents = function initializeEvents() {
        var self = this;
        self.$input
            .on('input', debounce(function () {
                if (self.hasChanged()) {
                    self.search();
                }
            }, self._debounceTimeout))
            .on('focus', function() {
                self.scrollToInput();
                self.search();
                self.activateBlackout();
            })
            .on('blur', debounce(function() {
                self.clearSearchedData();
                self.closeSearchView();
            }, self._debounceTimeout));
        self.$mobButton
            .on('click', function() {
                self.$parent.addClass('input-opened');
                self.$input.focus();
            });
        self.$closeBtn
            .on('click', function() {
                self.clearSearchedData();
                self.closeSearchView();
            })
    };

    SearchInput.prototype.search = function search() {
        var self = this;
        self.value = self.$input.val().trim();
        if (!self.value) {
            self.showHideResults(false);
        } else {
            self.requestPage = 0;
            self.requestSearch(self.value, function (products, navKeys, contacts) {
                self.products = products || [];
                self.navKeys = navKeys || [];
                self.contacts = contacts || [];
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
        var isEmtyVisible = !self.products.length && !self.navKeys.length && !self.contacts.length;
        var html = container({products: self.products, navKeys: self.navKeys, contacts: self.contacts, isEmpty: isEmtyVisible});
        self.$placeholder.html(html);
    };

    SearchInput.prototype.requestSearch = function requestSearch(searchValue, cb) {
        var self = this;
        $.get(
            '/api/search',
            {
                search: searchValue,
                page: self.requestPage,
                limit: self.requestLimit
            },
            function (data) {
                cb(data.products || [], data.navs || [], data.contacts || []);
            }
        );
    };

    SearchInput.prototype.showHideResults = function hideResults(isVisible) {
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
            $('body').scrollTop(110);
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
        self.showHideResults(false);
        self.$parent.removeClass('input-opened');
        self.deactivateBlackout();
    };


    return SearchInput;
})();
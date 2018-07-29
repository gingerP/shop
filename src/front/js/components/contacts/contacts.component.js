(function () {

    var MOBILE_PAGE_WIDTH = 960;

    function ContactsComponentV2(googleMap) {
        this.map = googleMap;
        this.$contactsList = $('#contacts-list');
        this.$tabs = this.$contactsList.find('.contacts-tabs');
        this.$contactsContentsList = this.$contactsList.find('.contact-tab-content');
        this.$contactsGroups = this.$contactsList.find('.contact-group');
        this.$tabsContentContainer = this.$contactsList.find('.contacts-tabs-content');
        this.allMarkersPositions = this.$contactsList.data('positions');
        this.initEvents(this.map);
    }

    ContactsComponentV2.prototype.initEvents = function initEvents() {
        var self = this;
        self.$contactsList
            .find('.contact-title')
            .each(function () {
                var link = $(this);
                var markers = link.data('markers');
                if (self.map && markers.length) {
                    link.on('click', function (event) {
                        self.map.focusOnMarkersPositions(markers, {zoom: 12});
                        event.preventDefault();
                    });
                }
            });
        self.$contactsList
            .find('.contact-more')
            .each(function () {
                var link = $(this);
                var data = link.data();
                var tabContent = self.$contactsList.find('.contact-tab-content[data-code="' + data.code + '"]');
                link.on('click', function (event) {
                    self.openContact(data.code, tabContent, data);
                    event.preventDefault();
                });
            });
        self.$contactsList
            .find('.contact-show-on-map')
            .on('click', function (event) {
                var link = $(this);
                var data = link.data();
                if (self.map) {
                    self.map.focusOnMarkerPosition([data.lat, data.lng]);
                }
                if ($(document).width() > MOBILE_PAGE_WIDTH) {
                    event.preventDefault();
                }
            });
        self.$contactsList
            .find('.contact-tab-content-close, .contact-back')
            .click(function (event) {
                self.$tabs.show();
                self.$contactsGroups.show();
                self.$tabsContentContainer.hide();
                self.showAllMarkers();
                event.preventDefault();
            });
    };

    ContactsComponentV2.prototype.showAllMarkers = function showAllMarkers() {
        if (this.allMarkersPositions && this.allMarkersPositions.length) {
            this.map.focusOnMarkersPositions(this.allMarkersPositions, {zoom: 7});
        }
    };

    ContactsComponentV2.prototype.tryToShowInitialContact = function tryToDetectInitialContact() {
        var pathName = window.location.pathname;
        var parts = pathName.split('/');
        if (parts.length) {
            var contactCode = parts[parts.length - 1];
            if (contactCode.indexOf('contacts') !== 0) {
                return this.openContact(contactCode);
            }
        }
        return false;
    };

    ContactsComponentV2.prototype.openContact = function openContact(code, tabContent, data) {
        if (!data) {
            var link = this.$contactsList.find('[data-code="' + code + '"]');
            if (!link.length) {
                return false;
            }
            data = link.data();
        }
        this.$tabs.hide();
        this.$contactsContentsList.hide();
        this.$tabsContentContainer.show();
        this.$contactsList.find('.contact-group:not(.contact-group-' + data.group + ')').hide();
        tabContent = tabContent || this.$contactsList.find('.contact-tab-content[data-code="' + data.code + '"]');
        tabContent.show();
        if (this.map && data.lat && data.lng) {
            this.map.focusOnMarkerPosition([data.lat, data.lng]);
        }
        return true;
    };

    var googleMap = new GoogleMapComponent(document.querySelector('#google-map'));
    var contacts = new ContactsComponentV2(googleMap);
    googleMap.postInit(function () {
        if (!contacts.tryToShowInitialContact()) {
            contacts.showAllMarkers();
        }
    });
})();

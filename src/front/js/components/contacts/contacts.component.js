(function () {

    var MOBILE_PAGE_WIDTH = 960;

    function ContactComponentV2(googleMap) {
        this.map = googleMap;
        this.$contactsList = $('#contacts-list');
        this.$tabs = this.$contactsList.find('.contacts-tabs');
        this.$contactsContentsList = this.$contactsList.find('.contact-tab-content');
        this.$tabsContentContainer = this.$contactsList.find('.contacts-tabs-content');
        this.allMarkersPositions = this.$contactsList.data('positions');
        this.initEvents(this.map);
    }

    ContactComponentV2.prototype.initEvents = function initEvents() {
        var self = this;
        self.$contactsList
            .find('.contact-more')
            .each(function () {
                var link = $(this);
                var data = link.data();
                var tabContent = self.$contactsList.find('.contact-tab-content[data-id=' + data.id + ']');
                link.on('click', function () {
                    self.$tabs.hide();
                    self.$contactsContentsList.hide();
                    self.$tabsContentContainer.show();
                    tabContent.show();
                    if (self.map) {
                        var contactsWidth = self.$contactsList.width();
                        var shift = Math.floor(contactsWidth / 2);
                        self.map.focusOnMarkerPosition([data.lat, data.lng], {shift: [-shift, 0]});
                    }
                });
            });
        self.$contactsList
            .find('.contact-show-on-map')
            .on('click', function (event) {
                var link = $(this);
                var data = link.data();
                if (self.map) {
                    var contactsWidth = self.$contactsList.width();
                    var shift = Math.floor(contactsWidth / 2);
                    self.map.focusOnMarkerPosition([data.lat, data.lng], {shift: [-shift, 0]});
                }
                if ($(document).width() > MOBILE_PAGE_WIDTH) {
                    event.preventDefault();
                }
            });
        self.$contactsList
            .find('.contact-tab-content-close')
            .click(function () {
                self.$tabs.show();
                self.$tabsContentContainer.hide();
                self.showAllMarkers();
            });
    };

    ContactComponentV2.prototype.showAllMarkers = function showAllMarkers() {
        if (this.allMarkersPositions && this.allMarkersPositions.length) {
            var contactsWidth = this.$contactsList.width();
            var shift = Math.floor(contactsWidth / 2);
            this.map.focusOnMarkersPositions(
                this.allMarkersPositions,
                {shift: [-shift, 0], zoom: 7}
            );
        }
    };

    var googleMap = new GoogleMapComponent(document.querySelector('#google-map'));
    googleMap.preInit(function (map, google, mapOptions) {
        mapOptions.mapTypeControl = true;
        mapOptions.mapTypeControlOptions = {
            style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
            position: google.maps.ControlPosition.RIGHT_TOP
        };
    });
    googleMap.postInit(function () {
        var contacts = new ContactComponentV2(googleMap);
        contacts.showAllMarkers();
    });


})();

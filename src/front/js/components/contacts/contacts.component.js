(function () {
    var CLASS_SELECTED = 'selected';
    var CLASS_SLIDE_LEFT = 'slide-left';
    var CLASS_SLIDE_RIGHT = 'slide-right';
    var CLASS_CONTACT_IMAGE = 'contact-image';

    function ContactComponent(contactDocument) {
        this.$contactDocument = $(contactDocument);
        this.data = this.$contactDocument.data();
        this.initSliderEvents();
        this.initMapEvents();
    }

    ContactComponent.prototype.initSliderEvents = function initSliderEvents() {
        var self = this;
        self.$images = self.$contactDocument.find('.' + CLASS_CONTACT_IMAGE);
        if (self.$images.length) {
            self.$slideLeft = self.$contactDocument.find('.' + CLASS_SLIDE_LEFT);
            self.$slideRight = self.$contactDocument.find('.' + CLASS_SLIDE_RIGHT);
            self.selectedIndex = self.getSelectedIndex();
            self.selectImage(self.selectedIndex);
            self.$slideLeft.on('click', function () {
                if (self.selectedIndex === 0) {
                    self.selectedIndex = self.$images.length - 1;
                } else {
                    self.selectedIndex--;
                }
                self.selectImage(self.selectedIndex);
            });
            self.$slideRight.on('click', function () {
                if (self.selectedIndex === self.$images.length - 1) {
                    self.selectedIndex = 0;
                } else {
                    self.selectedIndex++;
                }
                self.selectImage(self.selectedIndex);
            });
        }
    };

    ContactComponent.prototype.initMapEvents = function initMapEvents() {
        var self = this;
        self.mapButton = self.$contactDocument.find('.view_in_map_button, .contact-title-marker');
        if (self.mapButton.length) {
            self.mapButton.on('click', function click() {
                self._processMapClick();
            });
        }
    };

    ContactComponent.prototype.selectImage = function selectImage(selectedIndex) {
        this.$images.removeClass(CLASS_SELECTED);
        $(this.$images.get(selectedIndex)).addClass(CLASS_SELECTED);
    };

    ContactComponent.prototype.getSelectedIndex = function getSelectedIndex() {
        for (var i = 0; i < this.$images.length; i++) {
            if ($(this.$images.get(i)).hasClass(CLASS_SELECTED)) {
                return i;
            }
        }
        return 0;
    };

    ContactComponent.prototype.onLocalMapFocused = function onLocalMapFocused(callback) {
        this.onLocalMapFocusedCb = callback;
        return this;
    };

    ContactComponent.prototype._processMapClick = function _processMapClick() {
        var self = this;
        if (typeof self.onLocalMapFocusedCb === 'function') {
            self.onLocalMapFocusedCb([self.data.lat, self.data.lng], self._getMapInfoHtml());
        }
    };

    ContactComponent.prototype._getMapInfoHtml = function _processMapClick() {
        var self = this;
        var info = self.data.info;
        if (info) {
            var content = '<div style="font-size: 14px; font-weight: bold">' + info.address + '</div>';
            if (info.workingHours && info.workingHours.length === 2) {
                content += '<div style="margin: 5px 25px 0;">';
                content += '<span style="color: #a09ea2; font-size: 12px; font-weight: bold">время работы: </span>';
                content += '<span style="color: #414141; font-size: 12px; font-weight: bold">c ' + info.workingHours[0] + ' до ' + info.workingHours[1] + '</span>';
                content += '</div>';
            }
            if (info.weekend && info.weekend.length) {
                content += '<div style="margin: 5px 25px;">';
                content += '<span style="color: #a09ea2; font-size: 12px; font-weight: bold">выходные: </span>';
                content += '<span style="color: #414141; font-size: 12px; font-weight: bold">' + info.weekend.join(', ') + '</span>';
                content += '</div>';
            }
            if (info.prices && info.prices.length) {
                content += '<ul>';
                for (var i = 0; i < info.prices.length; i++) {
                    content += '<li>' + info.prices[i] + '</li>';
                }
                content += '</li>';
            }
            return content;
        } else {
            return 'Торговая точка';
        }
    };

    var contactImages = $('.contact-item');
    var contactsComponents = [];
    if (contactImages.length) {
        for (var i = 0; i < contactImages.length; i++) {
            contactsComponents.push(new ContactComponent(contactImages.get(i)));
        }
    }

    var googleMapContainer = document.querySelector('#google_map');
    if (googleMapContainer) {
        new GoogleMapComponent(googleMapContainer)
            .postInit(function (googleMap) {
                for (var i = 0; i < contactsComponents.length; i++) {
                    contactsComponents[i].onLocalMapFocused(function (position, text) {
                        googleMap.focusOn(position, text);
                    });
                }
            });

    }

})();

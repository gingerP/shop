(function () {

    function initializeMap(cb) {
        var googleMapContainer = document.querySelector('#main-page-map');
        if (googleMapContainer) {
            var googleCompnent = new GoogleMapComponent(googleMapContainer);

            googleCompnent.addOptionsProcessor(function (commonOptions) {
                commonOptions.mapTypeControl = true;
                commonOptions.mapTypeControlOptions = {
                    style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
                    mapTypeIds: ['roadmap', 'terrain'],
                    position: google.maps.ControlPosition.TOP_LEFT
                };
                commonOptions.zoomControl = true;
                commonOptions.zoomControlOptions = {
                    position: google.maps.ControlPosition.LEFT_CENTER
                };
                commonOptions.scaleControl = true;
                commonOptions.streetViewControl = true;
                commonOptions.streetViewControlOptions = {
                    position: google.maps.ControlPosition.LEFT_TOP
                };
                commonOptions.fullscreenControl = true;
                commonOptions.fullscreenControlOptions = {
                    position: google.maps.ControlPosition.LEFT_BOTTOM
                };
            });

            googleCompnent.postInit(cb);

        }
    }

    function initializeContacts(map) {
        var $contactsContainer = $('.main-page-contacts');
        var $tabsContents = $('.main-page-contact-group');
        var $tabs = $('.main-page-contacts-tab');
        var backgroundClasses = $contactsContainer.data('backgroundClasses') || [];
        var contacts = [];
        var contactsGroups = {};
        var focusedContactId;

        function extractContactsData() {
            var contactsViews = document.getElementsByClassName('main-page-contact');
            for (var i = 0; i < contactsViews.length; i++) {
                var view = contactsViews[i];
                var data = $(view).data();
                contacts.push(data);
                contactsGroups[data.groupId] = contactsGroups[data.groupId] || [];
                contactsGroups[data.groupId].push(data);
            }
        }

        function initializeEvents() {
            $('.main-page-contacts-tab').on('click', function () {
                var id = this.id;
                var $tabContent = $('#tab-content-' + id);
                var $tab = $(this);
                var data = $tab.data();
                if (backgroundClasses.length) {
                    $contactsContainer.removeClass(backgroundClasses.join(' '));
                }
                $contactsContainer.addClass(data.backgroundClass);
                $tabs.removeClass('opened');
                $tabsContents.removeClass('opened');
                $tabContent.addClass('opened');
                $tab.addClass('opened');
                focusedContactId = null;
                focusMapOnGroupId(id);
            });

            $('.main-page-contact').hover(AuUtils.debounce(function () {
                var $contactView = $(this);
                var contactInfo = $contactView.data('contact');
                var contactId = contactInfo.id;
                if (contactId !== focusedContactId) {
                    focusedContactId = contactId;
                    for (var i = 0; i < contacts.length; i++) {
                        var contact = contacts[i].contact;
                        if (contact.id === contactId) {
                            map.focusOnMarker(contact.markerInfo.marker, 17);
                            break;
                        }
                    }
                }
            }, 300));
        }

        function focusMapOnGroupId(groupId) {
            var group = contactsGroups[groupId];
            if (group && group.length) {
                var markers = [];
                for (var i = 0; i < group.length; i++) {
                    markers.push(group[i].contact.markerInfo.marker);
                }
                map.focusOnMarkers(markers);
            }
        }

        function initializeMarkers() {
            for (var i = 0; i < contacts.length; i++) {
                var contact = contacts[i].contact;
                contact.markerInfo = map.addMarker(contact.map, contact.address);
            }
        }

        function initializeFirstFocused() {
            var selectedGroup = document.querySelectorAll('.main-page-contacts-tab.opened');
            if (selectedGroup && selectedGroup.length) {
                var groupId = selectedGroup[0].id;
                focusMapOnGroupId(groupId);
            }
        }

        extractContactsData();
        initializeEvents();
        initializeMarkers();
        setTimeout(initializeFirstFocused, 500);
    }

    initializeMap(function (map) {
        initializeContacts(map);
    });

})();

(function () {
    function GoogleMapComponent(container) {
        var self = this;
        var googleScript = document.createElement('script');
        googleScript.setAttribute('async', '');
        googleScript.setAttribute('defer', '');
        googleScript.setAttribute(
            'src',
            'https://maps.googleapis.com/maps/api/js?key='
            + window.AugustovaApp.googleApiKey
            + '&callback=wakeUpGoogleMapComponent'
        );
        window.wakeUpGoogleMapComponent = function () {
            var mapOptions = {};
            self._preInitProcess(mapOptions);
            mapOptions.center = mapOptions.center || new google.maps.LatLng(53.621351, 23.867684);
            mapOptions.zoom = mapOptions.zoom || 10;
            mapOptions.mapTypeId = mapOptions.mapTypeId || google.maps.MapTypeId.ROADMAP;
            self._extendMapOptions(mapOptions);
            self.map = new google.maps.Map(container, mapOptions);
            self._postInitProcess();
        };
        document.body.appendChild(googleScript);
        self._postInitCbList = [];
        self._preInitCbList = [];
        self._mapOptionsProcessors = [];
        self._markers = [];
        self._markersInfo = [];
        self._defaultZoomLevel = 15;
        self._postInitCbList = [];
        self._infoWindows = [];
    }

    GoogleMapComponent.prototype._extendMapOptions = function _extendMapOptions(mapOptions) {
        var self = this;
        var index = 0;
        while (index < self._mapOptionsProcessors.length) {
            var processor = self._mapOptionsProcessors[index];
            if (typeof processor === 'function') {
                processor(mapOptions);
            }
            index++;
        }
    };

    GoogleMapComponent.prototype._postInitProcess = function _postInitProcess() {
        var self = this;
        self._postInitCbList.forEach(function (cb) {
            cb(self);
        });
    };

    GoogleMapComponent.prototype._preInitProcess = function _preInitProcess(mapOptions) {
        var self = this;
        self._preInitCbList.forEach(function (cb) {
            cb(self, google, mapOptions);
        });
    };

    GoogleMapComponent.prototype.addOptionsProcessor = function (processor) {
        var self = this;
        self._mapOptionsProcessors.push(processor);
    };

    GoogleMapComponent.prototype.preInit = function postInit(cb) {
        if (typeof cb === 'function') {
            var self = this;
            self._preInitCbList.push(cb);
        }
    };

    GoogleMapComponent.prototype.postInit = function postInit(cb) {
        if (typeof cb === 'function') {
            var self = this;
            self._postInitCbList.push(cb);
        }
    };

    GoogleMapComponent.prototype.focusOn = function focusOn(position, info) {
        var self = this;
        var marker = new google.maps.LatLng(position[0], position[1]);
        var infoWindow = new google.maps.InfoWindow();
        self.removeInfoWindows();
        infoWindow.setContent(info);
        infoWindow.setPosition(marker);
        infoWindow.open(self.map);
        self._infoWindows.push(infoWindow);
        self.map.setZoom(15);
        self.map.panTo(marker);
    };

    GoogleMapComponent.prototype.focusOnMarkerPosition = function focusOn(position, options) {
        this.removeMarkers();
        options = options || {};
        var marker = new google.maps.Marker({
            map: this.map,
            draggable: false,
            position: {lat: position[0], lng: position[1]}
        });
        this._markers.push(marker);
        this.map.setZoom(options.zoom || 15);
        var center = new google.maps.LatLng(position[0], position[1]);
        // using global variable:
        this.map.panTo(center);
        if (options.shift) {
            this.map.panBy(options.shift[0], options.shift[1]);
        }
    };

    GoogleMapComponent.prototype.focusOnMarkersPositions = function focusOn(positions, options) {
        this.removeMarkers();
        options = options || {};
        var xSum = 0;
        var ySum = 0;
        for (var index = 0; index < positions.length; index++) {
            var position = positions[index];
            var marker = new google.maps.Marker({
                map: this.map,
                draggable: false,
                position: {lat: position[0], lng: position[1]}
            });
            xSum += position[0];
            ySum += position[1];
            this._markers.push(marker);
        }
        this.map.setZoom(options.zoom || 15);
        var center = new google.maps.LatLng(xSum / positions.length, ySum / positions.length);
        // using global variable:
        this.map.panTo(center);
        if (options.shift) {
            this.map.panBy(options.shift[0], options.shift[1]);
        }
    };

    GoogleMapComponent.prototype.removeInfoWindows = function removeInfoWindows() {
        var self = this;
        if (self._infoWindows && self._infoWindows.length) {
            while (self._infoWindows.length) {
                var infoWindow = self._infoWindows.pop();
                infoWindow.close();
            }
        }
    };

    GoogleMapComponent.prototype.removeMarkers = function removeInfoWindows() {
        if (this._markers && this._markers.length) {
            while (this._markers.length) {
                this._markers.pop().setMap(null);
            }
        }
    };

    GoogleMapComponent.prototype.addMarkers = function addMarkers(markersParams) {
        var self = this;
        var createdMarkersInfo = [];
        for (var i = 0; i < markersParams.length; i++) {
            var marker = markersParams[i];
            createdMarkersInfo.push(self.addMarker(marker.map, marker.html));
        }
        return createdMarkersInfo;
    };

    GoogleMapComponent.prototype.addMarker = function addMarkers(pos, infoWindowContent) {
        var self = this;
        var marker = new google.maps.Marker({
            position: {lat: pos[0], lng: pos[1]},
            map: self.map
        });
        var infoWindow = new google.maps.InfoWindow({
            content: infoWindowContent
        });
        self._markersInfo.push({
            marker: marker,
            infoWindow: infoWindow
        });
        infoWindow.open(self.map, marker);
        return {
            marker: marker,
            infoWindow: infoWindow
        };
    };

    GoogleMapComponent.prototype.focusOnMarkers = function focusOnMarkers(markers) {
        if (markers.length) {
            var self = this;
            var bounds = new google.maps.LatLngBounds();
            var activeMarkersInfo = [];
            var markerInfo;
            for (var i = 0; i < self._markersInfo.length; i++) {
                markerInfo = self._markersInfo[i];
                if (markers.indexOf(markerInfo.marker) >= 0) {
                    bounds.extend(markerInfo.marker.position);
                    activeMarkersInfo.push(markerInfo);
                }
            }
            self.map.fitBounds(bounds);
            for (i = 0; i < activeMarkersInfo.length; i++) {
                markerInfo = activeMarkersInfo[i];
                markerInfo.infoWindow.open(self.map, markerInfo.marker);
            }
        }
    };

    GoogleMapComponent.prototype.focusOnMarker = function focusOnMarker(marker, zoom) {
        var self = this;
        var markerInfo;
        var preparedZoom = zoom || self._defaultZoomLevel;
        for (var i = 0; i < self._markersInfo.length; i++) {
            markerInfo = self._markersInfo[i];
            if (marker === markerInfo.marker) {
                self.map.panTo(marker.position);
                markerInfo.infoWindow.open(self.map, marker);
                self.map.setZoom(preparedZoom);
                break;
            }
        }
    };

    window.GoogleMapComponent = GoogleMapComponent;
})();

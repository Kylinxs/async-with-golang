(function () {
    var mapNumber = 0, currentProtocol = document.location.protocol;

    if (currentProtocol != 'http:' && currentProtocol != 'https:') {
        currentProtocol = 'https:';
    }

    function delayedExecutor(delay, callback)
    {
        var timeout;

        return function () {
            if (timeout) {
                clearTimeout(timeout);
                timeout = null;
            }

            timeout = setTimeout(callback, delay);
        };
    }

    function getBaseLayers()
    {
        var layers = [], tiles = jqueryTiki.mapTileSets, factories = {
            openstreetmap: function () {
                return new OpenLayers.Layer.OSM();
            },
            mapquest_street: function () {
                return new OpenLayers.Layer.XYZ(
                    "MapQuest OpenStreetMap",
                    "http://otile1.mqcdn.com/tiles/1.0.0/osm/${z}/${x}/${y}.png",
                    {sphericalMercator: true}
                );
            },
            mapquest_aerial: function () {
                return new OpenLayers.Layer.XYZ(
                    "MapQuest Open Aerial",
                    "http://oatile1.mqcdn.com/tiles/1.0.0/sat/${z}/${x}/${y}.png",
                    {sphericalMercator: true}
                );
            },
            google_street: function () {
                if (typeof google !== "undefined") {
                    return new OpenLayers.Layer.Google(
                        "Google Streets",
                        {numZoomLevels: 20}
                    );
                } else {
                    return null;
                }
            },
            google_satellite: function () {
                if (typeof google !== "undefined") {
                    return new OpenLayers.Layer.Google(
                        "Google Satellite",
                        {type: google.maps.MapTypeId.SATELLITE, numZoomLevels: 22}
                    );
                } else {
                    return null;
                }
            },
            google_hybrid: function () {
                if (typeof google !== "undefined") {
                    return new OpenLayers.Layer.Google(
                        "Google Hybrid",
                        {type: google.maps.MapTypeId.HYBRID, numZoomLevels: 20}
                    );
                }
            },
            google_physical: function () {
                if (typeof google !== "undefined") {
                    return new OpenLayers.Layer.Google(
                        "Google Physical",
                        {type: google.maps.MapTypeId.TERRAIN}
                    );
                } else {
                    return null;
                }
            },
            blank: function () {
                // Fake layer to hide all tiles
                var layer = new OpenLayers.Layer.OSM(tr('Blank'));
                layer.isBlank = true;
                return layer;
            /* Needs additional testing
            },
            visualearth_road: function () {
                return new OpenLayers.Layer.VirtualEarth(
                    "Virtual Earth Roads",
                    {'type': VEMapStyle.Road}
                );
            */
            }
        };

        if (tiles.length === 0) {
            tiles.push('openstreetmap');
        }

        $.each(tiles, function (k, name) {
            var f = factories[name];

            if (f) {
                layers.push(f());
            }
        });

        return layers;
    }

    function parseCoordinates(value) {
        var matching = value.match(/^(-?[0-9]*(\.[0-9]+)?),(-?[0-9]*(\.[0-9]+)?)(,(.*))?$/);

        if (matching) {
            var lat = parseFloat(matching[3]);
            var lon = parseFloat(matching[1]);
            var zoom = matching[6] ? parseInt(matching[6], 10) : 0;

            return {lat: lat, lon: lon, zoom: zoom};
        }

        return null;
    }

    function writeCoordinates(lonlat, map, fixProjection) {
        var original = lonlat;

        if (fixProjection) {
            lonlat = lonlat.transform(
                map.getProjectionObject(),
                new OpenLayers.Projection("EPSG:4326")
            );

            if (lonlat.lon < 0.01 && lonlat.lat < 0.01) {
                lonlat = original;
            }
        }

        return formatLocation(lonlat.lat, lonlat.lon, map.getZoom());
    }

    function formatLocation (lat, lon, zoom)
    {
        // Convert , decimal points - where those are used
        var strLon = '' + lon;
        strLon.replace(',', '.');
        var strLat = '' + lat;
        strLat.replace(',', '.');
        return strLon + ',' + strLat + ',' + zoom;
    }

    $.fn.createMap = function () {
        this.each(function () {
            var id = $(this).attr('id'), container = this, desiredControls;
            $(container).css('background', 'white');
            desiredControls = $(this).data('map-controls');
            if (desiredControls === undefined) {
                desiredControls = 'controls,layers,search_location,current_location,streetview,navigation';
            }

            desiredControls = desiredControls.split(',');

            var setupHeight = function () {
                var height = $(container).height();
                if (0 === height) {
                    height = $(container).width() / 4.0 * 3.0;
                }

                $(container).closest('.height-size').each(function () {
                    height = $(this).data('available-height');
                    $(this).css('padding', 0);
                    $(this).css('margin', 0);
                });

                $(container).height(height);
            };
            setupHeight();

            $(window).resize(setupHeight);

            if (! id) {
                ++mapNumber;
                id = 'openlayers' + mapNumber;
                $(this).attr('id', id);
            }

            setTimeout(function () {
                OpenLayers.ImgPath = "lib/openlayers/theme/dark/";
                var map = container.map = new OpenLayers.Map(id, {
                    controls: [],
                    theme: null
                });
                var layers = getBaseLayers();

                // these functions attempt to retrieve values for the style attributes,
                // falling back to others if not all options are specified
                // e.g. if "select-fill-color" is not provided it will use "fill-color", or just "color" attributes

                var getColor = function (feature, intent, type) {
                    return feature.attributes[intent + "-" + type + "-color"] ||
                        feature.attributes[intent + "-color"] ||
                        feature.attributes[type + "-color"] ||
                        feature.attributes["color"] ||
                        "#6699cc";
                };

                var getStyleAttribute = function (feature, intent, type, def) {
                    return feature.attributes[intent + "-" + type] ||
                        feature.attributes[type] ||
                        def;
                };

                container.defaultStyleMap = new OpenLayers.StyleMap({
                    "default": new OpenLayers.Style(OpenLayers.Util.applyDefaults({
                        cursor: "pointer"
                    }, OpenLayers.Feature.Vector.style['default']), {
                        context: {
                            getFillColor: function (feature) {
                                return getColor(feature, "default", "fill");
                            },
                            getStrokeColor: function (feature) {
                                return getColor(feature, "default", "stroke");
                            },
                            getStrokeWidth: function (feature) {
                                return getStyleAttribute(feature, "default", "stroke-width", 3);
                            },
                            getStrokeDashstyle: function (feature) {
                                return getStyleAttribute(feature, "default", "stroke-dashstyle", "solid");
                            },
                            getPointRadius: function (feature) {
                                return getStyleAttribute(feature, "default", "point-radius", 5);
                            },
                            getFillOpacity: function (feature) {
                                return getStyleAttribute(feature, "default", "fill-opacity", 0.5);
                            },
                            getStrokeOpacity: function (feature) {
                                return getStyleAttribute(feature, "default", "stroke-opacity", 0.9);
                            }
                        }
                    }),
                    "select": new OpenLayers.Style(OpenLayers.Util.applyDefaults({
                        cursor: "pointer"
                    }, OpenLayers.Feature.Vector.style['select']), {
                        context: {
                            getFillColor: function (feature) {
                                return getColor(feature, "select", "fill");
                            },
                            getStrokeColor: function (feature) {
                                return getColor(feature, "select", "stroke");
                            },
                            getStrokeWidth: function (feature) {
                                return getStyleAttribute(feature, "select", "stroke-width", 3);
                            },
                            getStrokeDashstyle: function (feature) {
                                return getStyleAttribute(feature, "select", "stroke-dashstyle", "solid");
                            },
                            getPointRadius: function (feature) {
                                return getStyleAttribute(feature, "select", "point-radius", 5);
                            },
                            getFillOpacity: function (feature) {
                                return getStyleAttribute(feature, "select", "fill-opacity", 0.9);
                            },
                            getStrokeOpacity: function (feature) {
                                return getStyleAttribute(feature, "select", "stroke-opacity", 0.9);
                            }
                        }
                    }),
                    "temporary": new OpenLayers.Style(OpenLayers.Util.applyDefaults({
                        cursor: "pointer"
                    }, OpenLayers.Feature.Vector.style['temporary']), {
                        context: {
                            getFillColor: function (feature) {
                                return getColor(feature, "temporary", "fill");
                            },
                            getStrokeColor: function (feature) {
                                return getColor(feature, "temporary", "stroke");
                            },
                            getStrokeWidth: function (feature) {
                                return getStyleAttribute(feature, "temporary", "stroke-width", 4);
                            },
                            getStrokeDashstyle: function (feature) {
                                return getStyleAttribute(feature, "temporary", "stroke-dashstyle", "solid");
                            },
                            getPointRadius: function (feature) {
                                return getStyleAttribute(feature, "temporary", "point-radius", 5);
                            },
                            getFillOpacity: function (feature) {
                                return getStyleAttribute(feature, "temporary", "fill-opacity", 0.3);
                            },
                            getStrokeOpacity: function (feature) {
                                return getStyleAttribute(feature, "temporary", "stroke-opacity", 0.9);
                            }
                        }
                    }),
                    "vertex": new OpenLayers.Style(OpenLayers.Util.applyDefaults({
                        fillColor: "#6699cc",
                        strokeColor: "#6699cc",
                        pointRadius: 5,
                        fillOpacity: ".7",
                        strokeDashstyle: "solid"
                    }, OpenLayers.Feature.Vector.style['temporary']))
                });

                var markerStyle = {
                    externalGraphic: "${url}",
                    graphicWidth: "${width}",
                    graphicHeight: "${height}",
                    graphicXOffset: "${offsetx}",
                    graphicYOffset: "${offsety}",
                    graphicOpacity: typeof window.chrome === "undefined" ? 0.9 : 1
                    // Google Chrome v34 makes some markers invisible if not 100% opaque
                }, vectorStyle = {
                    fillColor: "${getFillColor}",
                    strokeColor: "${getStrokeColor}",
                    strokeDashstyle: "${getStrokeDashstyle}",
                    strokeWidth: "${getStrokeWidth}",
                    pointRadius: "${getPointRadius}",
                    fillOpacity: "${getFillOpacity}",
                    strokeOpacity: "${getStrokeOpacity}"
                };

                container.defaultStyleMap.addUniqueValueRules("default", "intent", {
                    "marker": markerStyle, "vectors": vectorStyle
                });

                container.defaultStyleMap.addUniqueValueRules("select", "intent", {
                    "marker": markerStyle, "vectors": vectorStyle
                });

                container.defaultStyleMap.addUniqueValueRules("temporary", "intent", {
                    "marker": markerStyle, "vectors": vectorStyle
                });

                container.layer = layers[0];
                container.vectors = new OpenLayers.Layer.Vector(tr("Editable"), {
                    styleMap: container.defaultStyleMap
                });
                container.uniqueMarkers = {};
                container.layers = {};
                try {
                    map.addLayers(layers);
                    map.addLayer(container.vectors);
                } catch (e) {
                    console.log("Map error: problem adding layer " + e.message);
                }

                container.resetPosition = function () {
                    map.setCenter(new OpenLayers.LonLat(0, 0), 3);
                };
                container.resetPosition();

                function setupLayerEvents(vectors) {
                    vectors.events.on({
                        featureunselected: function (event) {
                            if (event.feature.executor) {
                                event.feature.executor();
                            }
                            $(container).setMapPopup(null);
                        },
                        featuremodified: function (event) {
                            if (event.feature.executor) {
                                event.feature.executor();
                            }
                        },
                        beforefeatureadded: function (event) {
                            if (! event.feature.attributes.color) {
                                event.feature.attributes.color = '#6699cc';
                            }
                            if (! event.feature.attributes.intent) {
                                event.feature.attributes.intent = "vectors";
                            }
                        }
                    });
                }

                setupLayerEvents(container.vectors);

                container.modeManager = {
                    modes: [],
                    activeMode: null,
                    addMode: function (options) {
                        var mode = $.extend({
                            name: tr('Default'),
                            icon: null,
                            events: {
                                activate: [],
                                deactivate: []
                            },
                            controls: [],
                            layers: []
                        }, options);

                        $.each(mode.layers, function (k, layer) {
                            layer.displayInLayerSwitcher = false;
                            layer.setVisibility(false);
                            map.addLayer(layer);
                        });

                        $.each(mode.controls, function (k, control) {
                            control.autoActivate = false;
                            map.addControl(control);
                        });

                        this.modes.push(mode);

                        this.register('activate', mode.name, mode.activate);
                        this.register('deactivate', mode.name, mode.deactivate);

                        if (! this.activeMode) {
                            this.activate(mode);
                        }

                        $(container).trigger('modechanged');

                        return mode;
                    },
                    switchTo: function (modeName) {
                        var manager = this;
                        $.each(this.modes, function (k, mode) {
                            if (mode.name === modeName) {
                                manager.activate(mode);
                            }
                        });
                    },
                    register: function (eventName, modeName, callback) {
                        $.each(this.modes, function (k, mode) {
                            if (mode.name === modeName && callback) {
                                mode.events[eventName].push(callback);
                            }
                        });
                    },
                    activate: function (mode) {
                        if (this.activeMode) {
                            this.deactivate();
                        }

                        this.activeMode = mode;

                        $.each(mode.controls, function (k, control) {
                            control.activate();
                        });
                        $.each(mode.layers, function (k, layer) {
                            layer.setVisibility(true);
                        });
                        $.each(mode.events.activate, function (k, f) {
                            f.apply([], container);
                        });

                        $(container).trigger('modechanged');
                    },
                    deactivate: function () {
                        if (! this.activeMode) {
                            return;
                        }

                        $.each(this.activeMode.controls, function (k, control) {
                            control.deactivate();
                        });
                        $.each(this.activeMode.layers, function (k, layer) {
                            layer.setVisibility(false);
                        });
                        $.each(this.activeMode.events.deactivate, function (k, f) {
                            f.apply([], container);
                        });

                        this.activeMode = null;
                    }
                };

                var defaultMode = {
                    controls: []
                };

                map.addControl(new OpenLayers.Control.Attribution());

                if (-1 !== $.inArray('coordinates', desiredControls)) {
                    map.addControl(new OpenLayers.Control.MousePosition({
                        displayProjection: new OpenLayers.Projection("EPSG:4326")
                    }));
                }

                if (layers.length > 0 && -1 !== $.inArray('scale', desiredControls)) {
                    map.addControl(new OpenLayers.Control.ScaleLine());
                }

                if (layers.length > 0 && -1 !== $.inArray('navigation', desiredControls)) {
                    defaultMode.controls.push(new OpenLayers.Control.NavToolbar());
                }

                if (layers.length > 0 && -1 !== $.inArray('controls', desiredControls)) {
                    if (-1 !== $.inArray('levels', desiredControls)) {
                        map.addControl(new OpenLayers.Control.PanZoomBar());
                    } else {
                        map.addControl(new OpenLayers.Control.PanZoom());
                    }
                }

                if (layers.length > 1 && -1 !== $.inArray('layers', desiredControls)) {
                    map.addControl(new OpenLayers.Control.LayerSwitcher());
                }

                var highlightControl, selectControl, vectorLayerList = [container.vectors];

                if ($(container).data("tooltips")) {
                    defaultMode.controls.push(highlightControl = new OpenLayers.Control.SelectFeature(vectorLayerList, {
                        hover: true,
                        highlightOnly: true,
                        renderIntent: "temporary",
                        clickout: true,
                        eventListeners: {
                            beforefeaturehighlighted: null,
                            featurehighlighted: function (evt) {

                                if (container.tooltipPopup) {
                                    map.removePopup(container.tooltipPopup);
                                    container.tooltipPopup = null;
                                }

                                if (evt.feature.layer.selectedFeatures.indexOf(evt.feature) > -1) {
                                    return;
                                }

                                var lonlat = map.getLonLatFromPixel(
                                    // get mouse position
                                    this.handlers.feature.evt.xy
                                );
                                var popup = new OpenLayers.Popup.Anchored(
                                    'myPopup',
                                    lonlat,
                                    new OpenLayers.Size(150, 18),
                                    "<small>" + evt.feature.attributes.content + "</small>",
                                    {size: {w: 14, h: 14}, offset: {x: -7, y: -7}},
                                    false
                                );
                                container.tooltipPopup = popup;
                                popup.autoSize = true;
                                popup.updateSize();
                                map.addPopup(popup);
                            },
                            featureunhighlighted: function (evt) {
                                if (container.tooltipPopup) {
                                    map.removePopup(container.tooltipPopup);
                                    container.tooltipPopup = null;
                                }
                            }
                        }
                    }));
                }

                defaultMode.controls.push(selectControl = new OpenLayers.Control.SelectFeature(vectorLayerList, {
                    onSelect: function (feature) {
                        if (container.tooltipPopup) {
                            map.removePopup(container.tooltipPopup);
                            container.tooltipPopup = null;
                        }
                        if (feature.attributes.url === container.markerIcons.loadedMarker["default"].url) {
                            feature.attributes.url = container.markerIcons.loadedMarker.selection.url;
                            feature.layer.redraw();
                        }
                        var type = feature.attributes.type
                            , object = feature.attributes.object
                            , lonlat = feature.geometry.getBounds().getCenterLonLat()
                            , loaded = false
                            ;

                        if (feature.attributes.itemId) {
                            type = 'trackeritem';
                            object = feature.attributes.itemId;
                        }

                        if (type && object) {
                            loaded = $(container).loadInfoboxPopup({
                                type: type,
                                object: object,
                                lonlat: lonlat,
                                content: feature.attributes.content,
                                close: function () {
                                    selectControl.unselect(feature);
                                },
                                feature: feature
                            });
                        }

                        if (! loaded && feature.attributes.content) {
                            var popup = new OpenLayers.Popup.FramedCloud('feature', lonlat, null, feature.attributes.content, null, true, function () {
                                $(container).setMapPopup(null);
                            });
                            popup.autoSize = true;

                            $(container).setMapPopup(popup);
                        }

                        if (feature.clickHandler) {
                            feature.clickHandler();
                        }
                    },
                    onUnselect: function (feature) {
                        if (feature.attributes.url === container.markerIcons.loadedMarker.selection.url) {
                            feature.attributes.url = container.markerIcons.loadedMarker["default"].url;
                            feature.layer.redraw();
                        }
                    }
                }));

                if (layers.length > 0 && -1 !== $.inArray('overview', desiredControls)) {
                    var overview = new OpenLayers.Control.OverviewMap({minRatio: 128, maxRatio: 256, maximized: true});
                    overview.desiredZoom = function () {
                        return Math.min(Math.max(1, map.getZoom() - 6), 3);
                    };
                    overview.isSuitableOverview = function() {
                        return this.ovmap.getZoom() === overview.desiredZoom() && this.ovmap.getExtent().contains(map.getExtent());
                    };
                    overview.updateOverview = function() {
                        overview.ovmap.setCenter(map.getCenter());
                        overview.ovmap.zoomTo(overview.desiredZoom());
                        this.updateRectToMap();
                    };

                    map.addControl(overview);
                }

                container.markerIcons = {
                    loadedMarker: {},
                    actionQueue: {},
                    loadingMarker: [],
                    loadMarker: function (name, src) {
                        this.loadingMarker.push(name);
                        this.actionQueue[name] = [];

                        var img = new Image(), me = this;
                        img.onload = function () {
                            var width = this.width, height = this.height, action;
                            me.loadedMarker[name] = {
                                intent: 'marker',
                                url: src,
                                width: width,
                                height: height,
                                offsetx: - width / 2,
                                offsety: - height
                            };

                            while (action = me.actionQueue[name].pop()) {
                                action();
                            }
                        };
                        $(img).on("error", function () {
                            console.log("Map error loading marker image " + src);
                            var index = container.markerIcons.loadingMarker.indexOf(src), action;
                            if (index > -1) {
                                container.markerIcons.loadingMarker.splice(index, 1);
                            }
                            while (action = me.actionQueue[name].pop()) {
                                action();
                            }
                        });
                        img.src = src;
                    },
                    createMarker: function (name, lonlat, callback) {
                        if (this.loadedMarker[name]) {
                            this._createMarker(name, lonlat, callback);
                            return;
                        }

                        if (-1 === $.inArray(name, this.loadingMarker)) {
                            this.loadMarker(name, name);
                        }

                        var me = this;
                        this.actionQueue[name].push(function () {
                            me._createMarker(name, lonlat, callback);
                        });
                    },
                    _createMarker: function (name, lonlat, callback) {
                        if (lonlat) {
                            var properties = $.extend(this.loadedMarker[name] || this.loadedMarker.default, {}), marker;
                            marker = new OpenLayers.Feature.Vector(
                                new OpenLayers.Geometry.Point(lonlat.lon, lonlat.lat),
                                properties
                            );
                            callback(marker);
                        }
                    }
                };

                container.getLayer = function (name) {
                    var vectors;

                    if (name) {
                        if (! container.layers[name]) {
                            vectors = container.layers[name] = new OpenLayers.Layer.Vector(name, {
                                styleMap: container.defaultStyleMap,
                                rendererOptions: {zIndexing: true}
                            });

                            container.map.addLayer(vectors);
                            vectorLayerList.push(vectors);
                            container.map.setLayerZIndex(vectors, vectorLayerList.length * 1000);
                            setupLayerEvents(vectors);

                            if (highlightControl && highlightControl.active) {
                                highlightControl.deactivate();
                                highlightControl.activate();
                            }
                            if (selectControl.active) {
                                selectControl.deactivate();
                                selectControl.activate();
                            }
                        }

                        return container.layers[name];
                    }

                    return container.vectors;
                };

                container.clearLayer = function (name) {
                    var vectors = container.getLayer(name);

                    var toRemove = [];
                    $.each(vectors.features, function (k, f) {
                        if (f && f.attributes.itemId) {
                            toRemove.push(f);
                        } else if (f && f.attributes.type && f.attributes.object) {
                            toRemove.push(f);
                        }
                    });
                    vectors.removeFeatures(toRemove);
                };

                container.markerIcons.loadMarker('default', 'lib/openlayers/img/marker.svg');
                container.markerIcons.loadMarker('selection', 'lib/openlayers/img/marker-gold.svg');

                if (navigator.geolocation && navigator.geolocation.getCurrentPosition) {
                    container.toMyLocation = $('<a/>')
                        .css('display', 'block')
                        .attr('href', '')
                        .click(function () {
                            navigator.geolocation.getCurrentPosition(function (position) {
                                var lonlat = new OpenLayers.LonLat(position.coords.longitude, position.coords.latitude).transform(
                                    new OpenLayers.Projection("EPSG:4326"),
                                    map.getProjectionObject()
                                );

                                map.setCenter(lonlat);
                                map.zoomToScale(position.coords.accuracy * OpenLayers.INCHES_PER_UNIT.m);

                                $(container).addMapMarker({
                                    lat: position.coords.latitude,
                                    lon: position.coords.longitude,
                                    unique: 'selection'
                                });
                            });
                            return false;
                        })
                        .text(tr('To My Location'));

                    if (-1 !== $.inArray('current_location', desiredControls)) {
                        $(container).after(container.toMyLocation);
                    }
                }

                container.searchLocation = $('<a/>')
                    .css('display', 'block')
                    .attr('href', '')
                    .click(function () {
                        var address = prompt(tr('What address are you looking for?'), "");

                        $(container).trigger('search', [ { address: address } ]);
                        return false;
                    })
                    .text(tr('Search Location'));

                if (-1 !== $.inArray('search_location', desiredControls)) {
                    $(container).after(container.searchLocation);
                }

                var field = $(container).data('target-field');
                var central = null, useMarker = true;

                if (field) {
                    field = $($(container).closest('form')[0][field]);

                    $(container).setupMapSelection({
                        field: field
                    });
                    var value = field.val();
                    central = parseCoordinates(value);

                    if (central) { // cope with zoom levels greater than what OSM layer[0] can cope with
                        var geLayer;
                        if (central.zoom > 19) {
                            geLayer = map.getLayersByName("Google Satellite");
                        } else if (central.zoom > 18) {
                            geLayer = map.getLayersByName("Google Streets");
                        }
                        if (geLayer) {
                            container.layer = geLayer[0];
                            map.setBaseLayer(container.layer);
                            map.baseLayer.setVisibility(true);
                        }
                    }
                }

                if ($(container).data('marker-filter')) {
                    var filter = $(container).data('marker-filter');
                    $(filter).each(function () {
                        var lat = $(this).data('geo-lat')
                            , lon = $(this).data('geo-lon')
                            , zoom = $(this).data('geo-zoom')
                            , extent = $(this).data('geo-extent')
                            , icon = $(this).data('icon-src')
                            , object = $(this).data('object')
                            , type = $(this).data('type')
                            , content = $(this).clone().data({}).wrap('<span/>').parent().html()
                            ;

                        if (! extent) {
                            if ($(this).hasClass('primary') || this.href === document.location.href) {
                                central = {lat: lat, lon: lon, zoom: zoom ? zoom : 0};
                            } else {
                                $(container).addMapMarker({
                                    type: type,
                                    object: object,
                                    lon: lon,
                                    lat: lat,
                                    content: content,
                                    icon: icon ? icon : null
                                });
                            }
                        } else if ($(this).is('img')) {
                            var graphic = new OpenLayers.Layer.Image(
                                $(this).attr('alt'),
                                $(this).attr('src'),
                                OpenLayers.Bounds.fromString(extent),
                                new OpenLayers.Size($(this).width(), $(this).height())
                            );

                            graphic.isBaseLayer = false;
                          
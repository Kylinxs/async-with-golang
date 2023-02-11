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

                        $(container).trigger('m
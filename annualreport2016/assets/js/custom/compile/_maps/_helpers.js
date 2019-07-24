import * as geojson from '../../libs/world.geo.json/countries.geo.json';
/**
 * Fire the document refresh, to auto refresh any
 * methods dependent on with or size of containers
 */
export function fireResize(){
    if (document.createEvent) { // W3C
        var ev = document.createEvent('Event');
        ev.initEvent('resize', true, true);
        window.dispatchEvent(ev);
    }
    else { // IE
        element=document.documentElement;
        var event=document.createEventObject();
        element.fireEvent("onresize",event);
    }
};

/**
 * Spit out a random hex code
 * @returns {string}
 */
export function getRandomColor() {
    var length = 6;
    var chars = '0123456789ABCDEF';
    var hex = '#';
    while(length--) hex += chars[(Math.random() * 16) | 0];
    return hex;
}

/**
 * Adjust the map zoom according to map size
 * @param map
 * @param ev
 */
export function setMinMaxZoomBasedOnWidth(map,ev = null)
{
    var _zoom = 0;
    var _current_size = (ev != null)?ev.newSize:map.getSize();
    if(_current_size.x >= 795)
    {
        _zoom = 2.2;
    }
    else if(_current_size.x > 480 && _current_size.x < 795)
    {
        _zoom = 1.48;
    }
    else if(_current_size.x > 480 && _current_size.x < 549)
    {
        _zoom = 1.2;
    }
    else if(_current_size.x <= 480 && _current_size.x > 370)
    {
        _zoom = 1;
    }
    else if(_current_size.x <= 370)
    {
        _zoom = 0;
    }
    else
    {
        _zoom = 2.2;
    }
    return _zoom;
    //map.setMinZoom(_zoom);
    //map.setMaxZoom(_zoom);
}

/**
 * Create a map using the ID given
 * @param map_id
 * @private
 */
export function _create_map(map_id,marker_data)
{
    //example command:
    // see: http://build-failed.blogspot.com/2012/11/zoomable-image-with-leaflet.html
    // gdal_translate -of vrt map-2.png temp.vrt;gdal2tiles.py -p raster -z 0-2 -w none temp.vrt
    /*
     *
     mandatory image sizes for certain zoom levels
     Zoom level 0: 256 px
     Zoom level 1: 512 px
     Zoom level 2: 1024 px
     Zoom level 3: 2048 px
     Zoom level 4: 4096 px
     Zoom level 5: 8192 px
     Zoom level 6: 16384 px
     */
    var southWest = L.latLng(-56.39566444471658,-152.05078125),
        northEast = L.latLng(71.1877539181316,141.67968750000003);

    var bounds = L.latLngBounds(southWest, northEast)

    var map = L.map(map_id,{
        //center: bounds.getCenter(),
        //maxBounds: bounds,
        //maxBoundsViscosity: 1.0,
        zoomControl: false,
        autoPan : false
    });
    map.fitBounds(bounds);

    //var map_layer = L.tileLayer('/assets/maps/map-4/temp/{z}/{x}/{y}.png', {
    //    minZoom: setMinMaxZoomBasedOnWidth(map),
    //    maxZoom: setMinMaxZoomBasedOnWidth(map),
    //    tms: true,
    //    noWrap: true
    //}).addTo(map);


    //https://stackoverflow.com/questions/28339414/leaflet-change-map-color
    var geo_json_1 = 'assets/js/custom/libs/world.geo.json/countries.geo.json';
    var geo_json_2 = 'assets/js/custom/libs/world.geojson/world.geojson';

    //$.getJSON(geo_json_1, function (geojson) { // load file

        L.geoJson(geojson, { // initialize layer with data
            style: function (feature) { // Style option
                //console.warn(feature);
                return {
                    'weight': 0.2,
                    'color': '#000',
                    'opacity': 0.20,
                    'fillColor': '#000',
                    'fillOpacity' : 0.2,
                    stroke: true,
                    className: `country-el country-id-${feature.id} country-name-${feature.properties.name.toLowerCase().replace(/\s/g, '-')}`
                }
            },
            noWrap: true
        }).addTo(map); // Add layer to map

        marker_data.forEach(function(element){
            L.circleMarker(element.latlng,
                {
                    color:'#ffffff',
                    opacity:1.0,
                    stroke:true,
                    fill:true,
                    fillColor: element.color,//getRandomColor(),
                    fillOpacity: 1.0,
                    weight: 0.8,
                    radius: 9,
                    className: element.class
                }
            ).on('click',function(el){
                    map.closePopup();
                    var _top = `
                        <div class="top clearfix">
                            <div class="clearfix country"><span class="country-circle" style="background-color: ${element.color};"></span><span class="name"> ${element.country}</span></div>
                            <div class="clearfix institution">
                                <a href="${element.institution.url}" target="_blank">${element.institution.institution_name}</a>
                            </div>
                        </div>`;
                    var _bottom = (element.blog_link.url)?`
                        <div class="bottom clearfix">
                            <div class="blog_link">
                                <a href="${element.blog_link.url}" target="_blank"><img src="assets/images/3_impact-global/map/blog-icon-01.svg" alt=""></a>
                            </div>
                        </div>`:'';
                    var hover_bubble = new L.Rrose({ offset: new L.Point(0,-10), closeButton: false, autoPan: false })
                        .setContent(`
                    <div class="clearfix map-marker-popup-content">
                        ${_top}
                        ${_bottom}
                    </div>
                `)
                        .setLatLng(element.latlng)
                        .openOn(map);
                })
                //.on('click', function(e){
                //    map.closePopup()
                //})
                .addTo(map)
        });

    //}); // END: load file


    map.dragging.disable();
    map.touchZoom.disable();
    map.doubleClickZoom.disable();
    map.scrollWheelZoom.disable();
    map.keyboard.disable();

    /* L.circleMarker([37.7576948,-122.4726193],
     circle_marker_defaults
     ).addTo(map)
     .on('mouseover mousemove',function(ev){
     map.closePopup();
     var hover_bubble = new L.Rrose({ offset: new L.Point(0,-10), closeButton: false, autoPan: false })
     .setContent('hello')
     .setLatLng(ev.latlng)
     .openOn(map);
     })
     .on('mouseout', function(e){
     map.closePopup()
     })
     */

    //add the markers


    map.on('click',function(ev){
    });


    //if the map resizes, check for the right zoom
    map.on('resize',function(ev){
        var _zoom = setMinMaxZoomBasedOnWidth(map,ev);
        map.setMinZoom(_zoom);
        map.setMaxZoom(_zoom);
    });

    // Disable drag and zoom handlers.

    map.setMinZoom(setMinMaxZoomBasedOnWidth(map));
    map.setMaxZoom(setMinMaxZoomBasedOnWidth(map));
    return map;
}
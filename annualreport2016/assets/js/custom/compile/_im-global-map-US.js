function getRandomColor() {
    var length = 6;
    var chars = '0123456789ABCDEF';
    var hex = '#';
    while(length--) hex += chars[(Math.random() * 16) | 0];
    return hex;
}

function _create_map(map_id)
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

    var map_layer = L.tileLayer('/assets/maps/map-4/temp/{z}/{x}/{y}.png', {
        minZoom: 0,
        maxZoom: 2,
        tms: true,
        noWrap: true
    }).addTo(map);

    //var latlngs = L.rectangle(bounds).getLatLngs();
    //L.polyline(latlngs[0].concat(latlngs[0][0])).addTo(map);
    //L.polyline(latlngs[0].concat(latlngs[0][0])).addTo(map);

    // Disable drag and zoom handlers.
    map.dragging.disable();
    map.touchZoom.disable();
    map.doubleClickZoom.disable();
    map.scrollWheelZoom.disable();
    map.keyboard.disable();

    //L.marker([37.7576948,-122.4726193]).addTo(map)
    //.bindPopup('A pretty CSS3 popup.<br> Easily customizable.');
    //.openPopup();
    var circle_marker_defaults = {
        color:'#ffffff',
        opacity:1.0,
        stroke:true,
        fill:true,
        fillColor:'#000000',
        fillOpacity: 1.0
    };
    L.circleMarker([37.7576948,-122.4726193],
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


    map.on('click',function(ev){
        L.circleMarker(ev.latlng,
            {
                color:'#ffffff',
                opacity:1.0,
                stroke:true,
                fill:true,
                fillColor: getRandomColor(),
                fillOpacity: 1.0
            }
        ).on('mouseover mousemove',function(ev){
            map.closePopup();
            var hover_bubble = new L.Rrose({ offset: new L.Point(0,-10), closeButton: false, autoPan: false })
                .setContent(`${ev.latlng}`)
                .setLatLng(ev.latlng)
                .openOn(map);
        }).on('mouseout', function(e){
                map.closePopup()
            })
            .addTo(map)

        //.bindPopup(`${ev.latlng}`);
    });

    function setMinMaxZoomBasedOnWidth(map,ev = null)
    {
        var _zoom = 0;
        var _current_size = (ev != null)?ev.newSize:map.getSize();
        console.warn(_current_size.x);
        if(_current_size.x >= 795)
        {
            _zoom = 2;
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
            _zoom = 2;
        }
        console.warn(_zoom);
        map.setMinZoom(_zoom);
        map.setMaxZoom(_zoom);
    }

    setMinMaxZoomBasedOnWidth(map);

    //map.setMaxZoom(2);
    map.on('resize',function(ev){
        setMinMaxZoomBasedOnWidth(map,ev);
    });



}

function _add_map()
{
    if(document.getElementById('map') )
    {
        _create_map('map');
    }
}

_add_map();


/*

Edgar Alejandro Saavedra Vallejo
Address:
718 Clement St., Apt 2
San Francisco, CA 94118
Unite States Of America
Email : edgarsaavedraa@gmail.com
Telephone Number: 1-773-627-8706
DOB of applicant: August 22, 1987
Passport Number: E14668607
Passport Issue Date (day-month-year) : 10-08-2015
Passport Expiry Date (day-month-year): 10-08-2021
Passport Country Of Issue: MEX
ETA Application Number: V313582428
Unique Client Identifier (UCI) : 1104390184
*/
function initialize() {
    var mapOptions = {
    center: { lat: 43.717744, lng: -79.378302},
    zoom: 12
    };
    
    var map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);
    
    for (var i = 1; i <= 15; i ++ ){
        var file = 'C' + pad(i, 2);
        map.data.loadGeoJson('http://107.170.110.165/Components/data/districtMaps/' + file + '.json');
    }
    for (var i = 1; i <= 11; i ++ ){
        var file = 'E' + pad(i, 2);
        map.data.loadGeoJson('http://107.170.110.165/Components/data/districtMaps/' + file + '.json');
    }
    for (var i = 1; i <= 10; i ++ ){
        var file = 'W' + pad(i, 2);
        map.data.loadGeoJson('http://107.170.110.165/Components/data/districtMaps/' + file + '.json');
    }
    
    map.data.setStyle({
                      fillColor: 'green',
                      strokeWeight: 3
                      });
    map.data.addListener('mouseover', function(event) {
                         document.getElementById('map_overlay').textContent =
                         event.feature.getProperty('AREA_MUNI');
                         });
    
}
google.maps.event.addDomListener(window, 'load', initialize);
document.addEventListener("DOMContentLoaded", function(event) {
                          document.getElementById('map_canvas').style.height = Math.max(document.documentElement.clientHeight, window.innerHeight || 0) + "px";
                          });


function pad(number, digits){
    var s = number + "";
    while (s.length < digits) s = "0" + s;
    return s;
    
}

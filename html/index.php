<!DOCTYPE html>
<html>
    <head>
        <style>
            body {margin:0;}
            #wrapper { position: relative; height:100%; width:100%}
            #search_functions{height:200px; padding:30px;}
            #map_canvas {height:100px; }
            #map_overlay { position: absolute; bottom: 30px; left: 30px; z-index: 99; background-color:white; padding:10px;}
        </style>
        <script type="text/javascript"src="https://maps.googleapis.com/maps/api/js?key="></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script type ="text/javascript" src="js/bootstrap.min.js"></script>
        <script type="text/javascript">
            function initialize() {
                var currentAverage;
                var maxGradient;
                var minGradient;
                
                var mapOptions = {
                    center: { lat: 43.717744, lng: -79.378302},
                    zoom: 11
                };
                
                var map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);

                for (var i = 1; i <= 15; i ++ ){
                    var file = 'C' + pad(i, 2);
                    map.data.loadGeoJson('Components/data/districtMaps/' + file + '.json');
                }
                for (var i = 1; i <= 11; i ++ ){
                    var file = 'E' + pad(i, 2);
                    map.data.loadGeoJson('Components/data/districtMaps/' + file + '.json');
                }
                for (var i = 1; i <= 10; i ++ ){
                    var file = 'W' + pad(i, 2);
                    map.data.loadGeoJson('Components/data/districtMaps/' + file + '.json');
                }

                map.data.setStyle(function(feature) {
                                  var gradientFloat = gradientPercent(minGradient,maxGradient,currentAverage[feature.getProperty("AREA_MUNI")]);
                                  return {
                                        fillColor: 'rgb(' + Math.floor(255*gradientFloat) + ',0,' + Math.floor(255*(1-gradientFloat)) + ')',
                                        strokeWeight: 2,
                                        fillOpacity: .55
                                  }
                });
                
                map.data.addListener('mouseover', function(event) {
                    document.getElementById('map_overlay').textContent = currentAverage[event.feature.getProperty("AREA_MUNI")];
                   
                });
                $.get("/Components/php/getAllAverage.php", {'type':'DETACHED HOUSES'},function( data ){
                    currentAverage = JSON.parse(data);
                    minGradient = Math.floor(currentAverage['MIN']/10000)*10000;
                    maxGradient = (Math.floor(currentAverage['MAX']/10000)+1) *10000;
                });
                
                

            }
            google.maps.event.addDomListener(window, 'load', initialize);
            document.addEventListener("DOMContentLoaded", function(event) {
                  document.getElementById('map_canvas').style.height = Math.max(document.documentElement.clientHeight, window.innerHeight || 0)-200 + "px";
            });
            
            
            function pad(number, digits){
                var s = number + "";
                while (s.length < digits) s = "0" + s;
                return s;
            }

            function gradientPercent(min, max, value){
                var minMaxDiff = max-min;
                var minValueDiff = value-min;
                return minValueDiff/minMaxDiff;
                
            }

            //$(housingType)
            </script>
            <link href="css/bootstrap.css" rel="stylesheet">
    </head>
    <body>
        <script>
        $(document).on('click', '#housingType li a', function () {
                       $("#housingDropdown .btn.btn-default.dropdown-toggle").text($(this).text());
                       //$("#map_overlay").text($(this).text());
                       //$.get("/Components/php/getAverage.php", {'sector':'C02', 'type':'DETACHED HOUSES'},function( data ){
                       //
                        //     });

            });

        </script>
        <div id="wrapper">
            <div id="search_functions">
                <div class="row">
                    <div class="col-md-4">
                        <div class="col-md-5">
                        <label style="font-size:150%">Housing Type: </label>
                        </div>
                        <div class="col-md-7">
                            <div id="housingDropdown" class="dropdown" >
                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" style="width:100%">Detached houses
                                    <span class="caret"></span>
                                </button></br>
                                <ul class="dropdown-menu" id="housingType" >
                                    <li><a role="menuitem" tabindex="-1" >Detached Houses</a></li>
                                    <li><a role="menuitem" tabindex="-1" >Semi-Detached Houses</a></li>
                                    <li><a role="menuitem" tabindex="-1" >Condominium Townhouses</a></li>
                                    <li><a role="menuitem" tabindex="-1" >Condominium Apartments</a></li>
                                    <li><a role="menuitem" tabindex="-1" >Link</a></li>
                                    <li><a role="menuitem" tabindex="-1" >Attached/Row/Townhouse</a></li>
                                    <li><a role="menuitem" tabindex="-1" >Co-Op Apartment</a></li>
                                    <li><a role="menuitem" tabindex="-1" >Detached Condominium</a></li>
                                    <li><a role="menuitem" tabindex="-1" >Co-Ownership Apartment</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="map_canvas"></div>
            <div id="map_overlay">?</div>
        </div>
    </body>
</html>

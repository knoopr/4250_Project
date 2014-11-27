<!DOCTYPE html>
<html>
    <head>
        <style>
            body {margin:0;}
            #wrapper {position:relative; height:100%; width:100%}
            #search_functions{position:absolute; top:30px; left:80px; z-index:99; width:400px;}
            #map_canvas {height:100px;}
            #map_text_overlay {position:absolute; bottom:30px; left:30px; z-index:99; background-color:white; padding:10px; border-style:solid; border-width:1px}
            #map_graph_overlay {position:absolute; bottom:30px; left:30px; z-index:99; background-color:white; height:200px; visibility:hidden; border-style:solid; border-width:1px}
            #map_gradient_overlay {position:absolute; right:30px; z-index:99; text-align:center; mborder-style:solid; border-width:1px}
            #text_max {background-color:white;}
            #text_min {background-color:white;}
            #gradient_img {opacity:.55; width:90px; height:350px;}
        </style>
        <script type="text/javascript"src="https://maps.googleapis.com/maps/api/js?key="></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script type ="text/javascript" src="js/bootstrap.min.js"></script>
        <script type ="text/javascript" src="js/lineGraph.js"></script>
        <script type="text/javascript" src="http://canvasjs.com/assets/script/canvasjs.min.js"></script></head>
        <script type="text/javascript">
            var map;
            var currentAverage;
            var maxGradient;
            var minGradient;
            var timeStamped;

            function initialize() {
                
                var mapOptions = {
                    center: { lat: 43.717744, lng: -79.378302},
                    zoom: 11
                };
                
                map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);

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
                
                map.data.addListener('mouseover', function(event) {
                     if (typeof currentAverage[event.feature.getProperty("AREA_MUNI")] === 'undefined'){
                        document.getElementById('map_text_overlay').textContent = "No matches";
                     }
                     else{
                         document.getElementById('map_text_overlay').textContent = formatCurrency(currentAverage[event.feature.getProperty("AREA_MUNI")]);
                     }
                   
                });
                map.data.addListener('click', function(event) {
                    var str = $("#selectionButton").text();
                    $.get("/Components/php/getAverageTimestamped.php", {'type':$.trim(str).toUpperCase(), 'sector':event.feature.getProperty("AREA_MUNI")},function( data ){
                            timeStamped = JSON.parse(data);
                            var keys = Object.keys(timeStamped);
                            var canvasObj = [];
                          
                            for (i in keys){
                                if (keys[i] != "MAX" && keys[i] != "MIN")
                                    canvasObj.push({"x": keys[i], "y":parseInt(timeStamped[keys[i]])});
                            }
                          document.getElementById("map_graph_overlay").style.visibility = "visible";
                          drawGraph(canvasObj, document.getElementById("map_graph_overlay"), timeStamped["MAX"], timeStamped["MIN"]);
                    });
                });
                
                google.maps.event.addListener(map, 'click', function(){
                      document.getElementById("map_graph_overlay").style.visibility = "hidden";
                });
                
                
                $.get("/Components/php/getAllAverage.php", {'type':'DETACHED HOUSES'}, function( data ){
                    currentAverage = JSON.parse(data);
                    changeMapColour();
                });
                
                

            }

            google.maps.event.addDomListener(window, 'load', initialize);
            document.addEventListener("DOMContentLoaded", function(event) {
                    document.getElementById('map_canvas').style.height = Math.max(document.documentElement.clientHeight, window.innerHeight || 0)+ "px";
                    document.getElementById('map_graph_overlay').style.width = Math.max(document.documentElement.clientWidth, window.innerWidth || 0) - 60 + "px";
                    document.getElementById('map_gradient_overlay').style.bottom = (Math.max(document.documentElement.clientHeight , window.innerHeight || 0)/2) -175 + "px"
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

            function changeMapColour(){
                if (currentAverage['MAX'] > 100000){
                    document.getElementById('text_max').textContent = formatCurrency((Math.floor(currentAverage['MAX']/10000)+1)*10000);
                    document.getElementById('text_min').textContent = formatCurrency(Math.floor(currentAverage['MIN']/10000)*10000);
                    minGradient = Math.floor(currentAverage['MIN']/10000)*10000;
                    maxGradient = (Math.floor(currentAverage['MAX']/10000)+1) *10000;
                }
                else{
                    document.getElementById('text_max').textContent = formatCurrency((Math.floor(currentAverage['MAX']/100)+1)*100);
                    document.getElementById('text_min').textContent = formatCurrency(Math.floor(currentAverage['MIN']/100)*100);
                    minGradient = Math.floor(currentAverage['MIN']/100)*100;
                    maxGradient = (Math.floor(currentAverage['MAX']/100)+1) *100;
                }
                    map.data.setStyle(function(feature) {
                        if (typeof currentAverage[feature.getProperty("AREA_MUNI")] === 'undefined'){
                              return {
                                  strokeWeight: 2,
                                  fillOpacity: 0
                              }
                        }
                        else{
                            var gradientFloat = gradientPercent(minGradient,maxGradient,currentAverage[feature.getProperty("AREA_MUNI")]);
                            return {
                                fillColor: 'rgb(' + Math.floor(255*gradientFloat) + ',0,' + Math.floor(255*(1-gradientFloat)) + ')',
                                strokeWeight: 2,
                                fillOpacity: .55
                            }
                        }
                    });
            }

            function formatCurrency(theNumber){
                var returnString = "";
                var modded = theNumber;
                
                while (modded != 0){
                    returnString = pad(modded%1000, 3) + "," + returnString;
                    modded = Math.floor(modded/1000);
                }
                while (returnString[0] == "0") returnString = returnString.substr(1, returnString.length);
                return "$" + returnString.substr(0, returnString.length-1);
                
            }

            $(document).on('click', '#housingType li a', function () {
               $("#selectionButton").text($(this).text());
               $.get("/Components/php/getAllAverage.php", {'type':$(this).text().toUpperCase()},function( data ){
                     currentAverage = JSON.parse(data);
                     changeMapColour();
                     });
            });


            </script>
            <link href="css/bootstrap.css" rel="stylesheet">
    </head>
    <body>
        <div id="wrapper">
            <div id="search_functions" >
                <div class="row">
                    <div class="col-md-5">
                        <label style="font-size:150%">Housing Type: </label>
                    </div>
                    <div class="col-md-7">
                        <div id="housingDropdown" class="dropdown" >
                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" style="width:100%" id="selectionButton">Detached Houses
                                <span class="caret"></span>
                            </button></br>
                            <ul class="dropdown-menu" id="housingType" >
                                <li role="presentation" class="dropdown-header">Buying/Selling</li>
                                <li><a role="menuitem" tabindex="-1" >Detached Houses</a></li>
                                <li><a role="menuitem" tabindex="-1" >Semi-Detached Houses</a></li>
                                <li><a role="menuitem" tabindex="-1" >Condominium Townhouses</a></li>
                                <li><a role="menuitem" tabindex="-1" >Condominium Apartment</a></li>
                                <li><a role="menuitem" tabindex="-1" >Link</a></li>
                                <li><a role="menuitem" tabindex="-1" >Attached/Row/Townhouse</a></li>
                                <li><a role="menuitem" tabindex="-1" >Co-Op Apartment</a></li>
                                <li><a role="menuitem" tabindex="-1" >Detached Condominium</a></li>
                                <li><a role="menuitem" tabindex="-1" >Co-Ownership Apartment</a></li>
                                <li role="presentation" class="divider"></li>
                                <li role="presentation" class="dropdown-header">Rentals</li>
                                <li><a role="menuitem" tabindex="1" >Bachelor Apartments</a></li>
                                <li><a role="menuitem" tabindex="1" >One-Bedroom Apartments</a></li>
                                <li><a role="menuitem" tabindex="1" >Two-Bedroom Apartments</a></li>
                                <li><a role="menuitem" tabindex="1" >Three-Bedroom Apartments</a></li>
                                <li><a role="menuitem" tabindex="1" >Bachelor Townhouses</a></li>
                                <li><a role="menuitem" tabindex="1" >One-Bedroom Townhouses</a></li>
                                <li><a role="menuitem" tabindex="1" >Two-Bedroom Townhouses</a></li>
                                <li><a role="menuitem" tabindex="1" >Three-Bedroom Townhouses</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div id="map_canvas"></div>
            <div id="map_text_overlay">?</div>
            <div id="map_graph_overlay" style="height:200px;"></div>
            <div id="map_gradient_overlay">
                <div id="text_max">?</div>
                <img id="gradient_img" src="/Components/data/Gradient.png">
                <div id="text_min">?</div>
            </div>
        </div>
    </body>
</html>

var exec = require('child_process').exec, fs = require('fs'), mapshaper = require('mapshaper');


var polygons = JSON.parse(fs.readFileSync('./4250_Project/html/Components/data/map.geojson'));


var municipalities = {};
var collection;
var merged;

for (var i = 0; i < polygons.features.length; i++){
    if (municipalities.hasOwnProperty(polygons.features[i].properties.AREA_MUNI)){
        municipalities[polygons.features[i].properties.AREA_MUNI].push(i);
    }
    else{
        if (polygons.features[i].properties.AREA_MUNI != undefined){
            municipalities[polygons.features[i].properties.AREA_MUNI] = [i];
        }
        else{
            console.log(polygons.features[i].properties);
        }
        
    }
}


for (zone in municipalities){
    mergedInfo=[]
    for (i in municipalities[zone]){
        mergedInfo.push({"geometry":{"type":"Polygon","coordinates":[polygons.features[municipalities[zone][i]].geometry.coordinates[0]]},"type":"Feature","properties":{"AREA_MUNI":zone}});
    }

    toBeOutput = {"type":"FeatureCollection","features":mergedInfo}
    
    file = zone + ".geojson";

    fs.writeFile(file, JSON.stringify(toBeOutput),  function (err) {
         if (err) throw err;
     });
}
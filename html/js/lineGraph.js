function drawGraph(data, div, maxValue, minValue){
    var timestamp;
    var date;
    var maxDate = new Date(0,0,0,0,0);
    var minDate = new Date(2200,0,0,0,0);
    var height = div.style.height;
    var width = div.style.width;
    
    for (var i = 0 ; i < data.length; i++){
        t = data[i]["x"].split(/[- :]/);
        date = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
        if (date > maxDate)
            maxDate = date;
        if (date < minDate)
            minDate = date;
        data[i]["x"] = date;
    }
    
    data = sortTimestamp(data);
    
    var chart = new CanvasJS.Chart(div.id,
       {
       title: {
       text: "Monthly Average Price"
       },
       data: [
              {
              type: "area",
              color : "red",
              dataPoints: data
              }
              ]
       });
    
    chart.render();
    
    /*if (document.getElementsByTagName("svg").length < 2){
        var svg = d3.select(div)
        .append("svg:svg")
        .attr("width", "100%")
        .attr("height", "100%")
        .attr("id", "line_graph")
        .append("g");
        
        var priceLine = d3.svg.area().
        x(function(d) { return d.Timestamp; }).
        y(function(d) { return d.Price; });
        
        svg.append("svg:rect").
                   attr("x", 0).
                   attr("y", 0).
                   attr("height", height).
                   attr("width", width).
                   attr("fill", "lightyellow");
        
        svg.append("path")
        .datum(d3Obj)
        .attr("class", "line")
        .attr("d", priceLine)
        .attr("fill","blue")
    }*/
    
    
    //else
    //    console.log("good");
}


//YAY bubble sort /s
function sortTimestamp(data){
    var sorted = []
    var split = 0;
    
    
    for (element in data){
        if (sorted.length == 0)
            sorted.push(data[element]);
        else{
            for (var i = 0; i < sorted.length; i ++){
                if (data[element].x < sorted[i].x){
                    break;
                }
            }
            sorted.splice(i,0,data[element]);
        }
    }
    
    return sorted;
    
}




map.on('click', function (evt) {

    //console.log(ol.proj.transform(evt.coordinate, 'EPSG:3857', 'EPSG:4326'));
    $("input[name=position]").val(evt.coordinate);
    feature.setGeometry(new ol.geom.Point(evt.coordinate))
    $("input[name=level]").val(map.getView().getZoom());
   //console.log(map.getView().getZoom());
})

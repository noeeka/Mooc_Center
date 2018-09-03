var sourceFeature1 = new ol.source.Vector({
    wrapX: false
});
var layerFeature1 = new ol.layer.Vector({
    source: sourceFeature1,
    minResolution: epsg_4326[min_zoom-1],
    maxResolution: epsg_4326[min_zoom-2] //8级显示a，不含7
});

function showLayer1() {

    // $.ajax({
    //     type: "get",
    //     dataType: "json",
    //     url: "./data/layer3.json",
    //     data: {},
    //     success: function (result) {

    // var result = features_data;
    $.ajax({
        url: '/api/venue/map',
        dataType: 'json',
        success: function (result) {
            if (result.status == 1) {
                result = result.data;
                for (var i = 0; i < result.length; i++) {
                    // var geom = new ol.geom.Point(result[i].pos);
                    //console.log(result[i].site_level);
                    // console.log(filterType);
                    if (result[i].site_level <= 1  ) {

                        var geom = new ol.geom.Point(result[i].position.split(','));

                        var feature = new ol.Feature({
                            geometry: geom
                        });


                        var style = new ol.style.Style({
                            image: new ol.style.Icon({
                                size: [result[i].index_img_size.width, result[i].index_img_size.height],
                                src: "/" + result[i].index_img
                            }),
                            text: new ol.style.Text({
                                font: '14px bold',
                                offsetY: 0.5 * result[i].index_img_size.height
                            })
                        });
                        style.getText().setText(result[i].name);
                        feature.setStyle(style);

                        feature.set("id", result[i].id);
                        sourceFeature1.addFeature(feature);
                    }


                }
            } else {
                console.log('场馆加载失败');
            }
        }
    });
};


map.addLayer(layerFeature1);
showLayer1();

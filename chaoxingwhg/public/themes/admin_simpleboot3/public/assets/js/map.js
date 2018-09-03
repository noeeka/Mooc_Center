$("#position").click(function(event) {
    //获取首页图标
    var index_img=$("input[name=index_img]").val();
    if(index_img.indexOf('uploads')!=-1){
        index_img='/'+index_img;
    }

    if(index_img.length>0){
        layerFeature.setStyle(new ol.style.Style({
            image: new ol.style.Icon({
                src:index_img
            })}))
    }else{
        alert('请上传首页图标');
        return false;
    }
   $('.mask').css('display', 'block');
   $('#index-position').css('display', 'block');
   map.updateSize();
});
$('.close-mask').click(function(){
   $('.mask').css('display', 'none');
   $('#index-position').css('display', 'none');
});
$(".baocun1").click(function () {
    var position= $("input[name=position]").val();
    if(position.length <=0){
        alert('请选择位置');
        return false;
    }
    $('.mask').css('display', 'none');
    $('#index-position').css('display', 'none');
})
$("input[name=position]").val
var controls = ol.control.defaults({
    attributionOptions: {
        collapsible: true
    }
}).extend([
    new ol.control.MousePosition({
        coordinateFormat: ol.coordinate.createStringXY(4),
        projection: 'EPSG:4326',
        // className: 'custom-mouse-wrapper',
        // target: document.getElementById('mouse-position')
    })
]);


//geoserver发布的
var geoserverMap = new ol.layer.Tile({
    title: "底图",
    source: new ol.source.TileWMS({
      /*  // projection:'EPSG:3857',
        url: "http://140.210.6.200:8080/geoserver/wms",
        // url:"http://localhost:8080/geoserver/gwc/service/wms",
        params: {
            request: "GetMap",
            version: '1.1.0',
            service: 'WMS',
            srs:'EPSG:4326',
            tiled: true,
            styles: '',
            // bbox:'113.5,24.39999999999995,118.59986282578879,30.2',
            layers: 'culture:jiangxi1',//dwy1,pinjie,bou2_4p,chn_adm3,jiangxi1,jinzita
            //tilesOrigin: -124.73142200000001 + "," + 24.955967
        }*/
        url: "http://120.92.71.181:8080/geoserver/culture/wms",
        // url:"http://localhost:8080/geoserver/culture/gwc/service/wms",
        params: {
            'request': "GetMap",
            'VERSION': '1.1.0',
            'service': 'WMS',
            srs:'EPSG:4326',
            tiled: true,
            styles: '',
            // bbox:'113.5,24.39999999999995,118.59986282578879,30.2',
            layers:map_address,//dwy1,pinjie,bou2_4p,chn_adm3,jiangxi1,jinzita
            //tilesOrigin: -124.73142200000001 + "," + 24.955967
        }
    })
})
var epsg_4326 = [0.703125, 0.3515625, 0.17578125, 0.087890625, 0.0439453125, 0.02197265625,
    0.010986328125, 0.0054931640625, 0.00274658203125, 0.001373291015625, 6.866455078125E-4,
    3.4332275390625E-4, 1.71661376953125E-4, 8.58306884765625E-5, 4.291534423828125E-5,
    2.1457672119140625E-5, 1.0728836059570312E-5, 5.364418029785156E-6, 2.682209014892578E-6,
    1.341104507446289E-6, 6.705522537231445E-7, 3.3527612686157227E-7
]

var map = new ol.Map({
    target: 'index-map',
    // controls: controls,
    layers: [geoserverMap],
    view: new ol.View({
        center:[map_center_pc_x ,map_center_pc_y ],//ol.proj.transform([115.9147, 27.3555], 'EPSG:4326', 'EPSG:3857'),
        projection: 'EPSG:4326',
        // extent:[73.2516174316406,15.5910949707031,135.080001831055,53.7497673034668],
        zoom: min_zoom,
        minZoom: min_zoom,
        maxZoom: max_zoom,//11，14
        extent:[map_scope1,map_scope2,map_scope3,map_scope4],
        // resolutions:epsg_3857
    })
});
var feature=new ol.Feature({
    geometry: new ol.geom.Point([0, 0])
});
var sourceFeature = new ol.source.Vector({
    wrapX: false,
    features:[feature]
});
console.log(1111);
var layerFeature = new ol.layer.Vector({
    source: sourceFeature,
    style: new ol.style.Style({
            image: new ol.style.Icon({
                src: '/static/admin/culmap/img/cg_icon.png'
            }),
         /*   text:new ol.style.Text({
                font:'14px',
                offsetY:105*0.5,//105 图片高度
                text:'一个场馆'
            })*/
        })
});
map.addLayer(layerFeature);
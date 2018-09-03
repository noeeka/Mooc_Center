var venue =0;//场馆id
var area =0;//区域id
var perform_type=0;//活动类型id
var page =1;
var tab1LoadEnd = false;
var itemIndex = 1;
$(function () {
    filter();


    var dropload=$('.huodong').dropload({
        scrollArea: window,
        domDown:{
            domNoData : '<div class="dropload-noData">已经到底啦~(>_<)~~</div>'
        },
        loadDownFn: function(me) {
            if (itemIndex == 1 ||  itemIndex == 2 ){
                // 加载按默认和最新发布排序的数据
                $.ajax({
                    url:'/api/culture',
                    type:'post',
                    data:{'page':page,'venue':venue,'area':area,'typeid':perform_type,'type':2,'len':10},
                    dataType:'json',
                    success:function (res) {
                        if(res.status ==1){
                            page++; //页数加1
                            if (res.data.list.length < 10) {
                                // 再往下已经没有数据
                                tab1LoadEnd = true;
                                // 锁定
                                me.lock();
                                // 显示无数据
                                me.noData();
                            }
                            var myTemplate = Handlebars.compile($("#list").html());
                            $("#lib").append(myTemplate(res.data.list));
                            me.resetload();
                        }
                    }
                })
            }
        }
    })

//获取场馆，区域，类型
function filter() {
    $.ajax({
        url:'/api/resourcelib',
        type:'post',
        dataType:'json',
        success:function (result) {
            if(result.status ==1){
                var venue1=result.data.venue;
                var area1 =result.data.area;
                var perform_type1=result.data.perform_type;
                //热门场馆
                $("#gradew").html("");
                var venue_html="<li onclick=\"grade1(this)\" data-venue='0'>全部</li>";
                $.each(venue1,function (i,v) {
                    venue_html+='<li onclick="grade1(this)" data-venue='+v.id+'>'+v.name+'</li>';
                })
                $("#gradew").html(venue_html);

                //区域
                $("#Categorytw").html("");
                $("#Categorytw").next().html("");
                var area_html="<li onclick=\"Categorytw(this)\" data-area='0'>全部</li>";
                $.each(area1,function (i,v) {
                    area_html+='<li onclick="Categorytw(this)" data-area='+v.id+'>'+v.name+'</li>';

                })
                $("#Categorytw").html(area_html);
                $("#Categorytw").find('li').click(function ( ) {
                    areaid = $(this).index();
                    var areason1 = 0;
                    if(areaid != 0) {
                        var areason1 = result.data.area[areaid - 1].son;
                    }
                    var id = $(this).data('area');
                    var name = $(this).html();
                    //获取二级区域
                    $("#Categoryt").html("");
                    $("#Categoryt").next().html("");
                    var area2_html = "<li onclick=\"Categoryt(this)\" data-area3="+id+">"+name+"</li>";
                    $.each(areason1, function (i, v) {
                        area2_html += '<li onclick="Categoryt(this)" data-area3=' + v.id + '>' + v.name + '</li>';

                    })
                    $("#Categoryt").html(area2_html);


                    //子区域点击获取id
                    $('#Categoryt').children('li').each(function () {
                        $(this).click(function () {
                            area = $(this).data('area3');
                            get_repository();


                        })

                    })

                })


                //类型
                $("#Sort-Sort").html("");
                var perform_type_html="<li onclick=\"Sorts(this)\" data-type='0'>全部</li>";
                $.each(perform_type1,function (i,v) {
                    perform_type_html+='<li onclick="Sorts(this)" data-type='+v.id+'>'+v.name+'</li>';
                })
                $("#Sort-Sort").html(perform_type_html);


                //场馆点击获取id
                $('#gradew').children('li').click(function(){
                    venue = $(this).data('venue');
                    get_repository();
                })




                //类型点击获取id
                $('#Sort-Sort').children('li').click(function(){
                    perform_type = $(this).data('type');
                    get_repository();
                })



            }
        }

    })
}
//获取活动列表
function get_repository() {
    page = 1;

    $.ajax({
        url:'/api/culture',
        type:'post',
        dataType:'json',
        data:{'page':page,'venue':venue,'area':area,'typeid':perform_type,'type':2,'len':10},
        success:function(res){
            if(res.status==1){

                page++;
                if (res.data.list.length < 10) {
                    // 再往下已经没有数据
                    tab1LoadEnd = true;
                    // 锁定
                    dropload.lock();
                    // 显示无数据
                    dropload.noData();
                }else{
                    dropload.unlock();
                    dropload.noData(false);
                }
                var myTemplate = Handlebars.compile($("#list").html());
                $("#lib").html(myTemplate(res.data.list));
                dropload.resetload();
            }



        }
    })
}
})
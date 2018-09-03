$(function () {
    //设置title
    title2(85);
    //默认和最新分页
    var pg=1;
    //区域
    var area=0;
    //类型
    var venuetype=0;
    //1:默认和最新排序，2：人气排序
    var order='publish_time';
    var len=10;

    var itemIndex = 1;
    var tab1LoadEnd = false;

    var template=Handlebars.compile($("#activity_list").html());

    var dropload=$('.huodong').dropload({
        scrollArea: window,
        domDown:{
            domNoData : '<div class="dropload-noData">已经到底啦~(>_<)~~</div>'
        },
        loadDownFn: function(me) {
            if (itemIndex == 1 ||  itemIndex == 2 ){
                // 加载按默认和最新发布排序的数据
                $.ajax({
                    url:'/api/venue',
                    type:'post',
                    data:{page:pg,area_id:area,venue_type:venuetype,order:order,len:len},
                    dataType:'json',
                    success:function (result) {
                        if(result.status ==1){
                            pg++; //页数加1
                            if (result.data.list.length < 6) {
                                // 再往下已经没有数据
                                tab1LoadEnd = true;
                                // 锁定
                                me.lock();
                                // 显示无数据
                                me.noData();
                            }
                            $(".huodongList").append(template(result.data.list))
                            me.resetload();
                        }
                    }
                })
            }
        }
    })

    //区域
    filter(dropload,template);
    //场馆类型
    venue_type(dropload,template);
    //切换排序
    $('.screenTj').children('li').each(function() {
        $(this).click(function () {
            if($(this).index()!=0){
                $('.screenTj').children('li').removeClass('active');
                $(this).addClass('active');
                tab1LoadEnd=false;
                // 如果数据没有加载完
                if (!tab1LoadEnd) {
                    // 解锁
                    dropload.unlock();
                    dropload.noData(false);
                } else {
                    // 锁定
                    dropload.lock('down');
                    dropload.noData();
                }
                pg=1;
                if($(this).index()==3){
                    order='page_view';
                }else{
                    order='publish_time';
                }
                get_data(dropload,template);
            }
        })
    })

    //获取活动
    function  get_data(dropload,template) {
        $.ajax({
            url:'/api/venue',
            type:'post',
            data:{page:pg,area_id:area,venue_type:venuetype,order:order,len:len},
            dataType:'json',
            success:function (result) {
                if(result.status ==1){
                    console.log(111);
                    console.log(result);
                    pg++; //页数加1
                    if (result.data.list.length < 6) {
                        // 再往下已经没有数据
                        tab1LoadEnd = true;
                        // 锁定
                        dropload.lock();
                        // 显示无数据
                        dropload.noData();
                    }
                    $(".huodongList").html("");
                    //渲染数据
                    $(".huodongList").append(template(result.data.list))
                    dropload.resetload();
                }else{
                    $('#huodongList').html('');
                    dropload.resetload();
                }
            }
        })
    }

    //获取场馆，区域，类型
    function filter(dropload,template) {

        $.ajax({
            url:'/api/filter',
            type:'post',
            dataType:'json',
            async:false,
            success:function (result) {
                if(result.status ==1){
                    var  data_area =result.data.area;

                    //区域
                    $("#Categorytw").html("");
                    var area_html= '<li onclick="Categorytw(this)" data-area="0">全部</li>';
                    var area2_html="";

                    $.each(data_area,function (i,v) {
                        area_html+='<li onclick="Categorytw(this)" data-area='+v.id+' data-son='+JSON.stringify(v.son)+'>'+v.name+'</li>';
                    })
                    $("#Categorytw").html(area_html);

                    $("#Categorytw").find('li').each(function () {
                        $(this).click(function () {
                            $(".Category-t").html("");
                            area2_html='<li onclick="Categoryt(this)" data-area1="'+$(this).data('area')+'">'+$(this).html()+'</li>';
                            var son=$(this).data('son');
                            for(var i in son){
                                area2_html+='<li onclick="Categoryt(this)" data-area1='+son[i].id+'>'+son[i].name+'</li>';
                            }
                            $(".Category-t").html(area2_html);

                            //二级区域
                            $(".Category-t").children('li').each(function ( ) {
                                $(this).click(function () {
                                    pg =1;
                                    area=$(this).data('area1');
                                    get_data(dropload,template);
                                })
                            })
                        })
                    })


                }
            }
        })
    }

    function venue_type(dropload,template) {

        $.ajax({
            url:'/api/venue/venue_type',
            type:'post',
            dataType:'json',
            async:false,
            success:function (result) {
                //类型
                $("#Sort-Sort").html("");
                var venue_type_html = '<li data-type="0" onclick="Sorts(this)">全部</li>';
                $.each(result.data, function (i, v) {
                    venue_type_html += '<li onclick="Sorts(this)" data-type=' + v.id + '>' + v.name + '</li>';
                })
                $("#Sort-Sort").html(venue_type_html);
                $("#Sort-Sort").children('li').each(function () {
                    $(this).click(function () {
                        pg = 1;
                        venuetype = $(this).data('type');
                        get_data(dropload, template);
                    })
                })

            }
        })

    }

})





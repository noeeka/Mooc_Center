var venue =0;//场馆id
var area =0;//区域id
var status=0;//活动类型id
var page =1;
var tab1LoadEnd = false;
var searchStartTime = 0;
var searchEndTime = 0;
var itemIndex = 1;
$(function() {
    $.date('#date2');
    $.date('#date3');
    filter();

    Handlebars.registerHelper("compare" , function(v1, v2, options){
        if(v1 == v2){
            return options.fn(this);
        } else {
            return options.inverse(this);
        }
    });

    $('#search').on('click' , function () {
        if ($('#date2').val() == 0 || $('#date3').val() == 0) {
            alert("请选择搜索时间");
            return;
        }
        searchStartTime = Date.parse(new Date($('#date2').val()) ) / 1000 - 3600 * 8;
        searchEndTime = Date.parse(new Date($('#date3').val())) / 1000 + 3600 * 16 ;
        get_repository(1);
    });

    Handlebars.registerHelper('status', function(items, fn) {
        var key = items.data.key;
        var status = items.data.root[key].status;

        return status == 1 ?"已完成" : "已预约";
    });
    Handlebars.registerHelper('apply_time', function(items, fn) {
        var key = items.data.key;
        var start = new Date();
        var end = new Date();

        start.setTime(items.data.root[key].start_time * 1000);
        end.setTime(items.data.root[key].end_time * 1000);

        return (start.getMonth()+1) + '月' + start.getDate() + '号';
    });
    Handlebars.registerHelper('start_time', function(items, fn) {
        var key = items.data.key;
        var start = new Date();
        start.setTime(items.data.root[key].start_time * 1000);
        var Minutes = start.getMinutes();
        if(Minutes < 10) {
            Minutes = '0' + Minutes;
        }
        return start.getHours() + ":" + Minutes;
    });
    Handlebars.registerHelper('end_time', function(items, fn) {
        var key = items.data.key;
        var end = new Date();

        end.setTime(items.data.root[key].end_time * 1000);

        var Minutes = end.getMinutes();
        if(Minutes < 10) {
            Minutes = '0' + Minutes;
        }
        return end.getHours() + ":" + Minutes;
    });

    var dropload = $('.sources').dropload({
        scrollArea: window,
        domDown:{
            domNoData : '<div class="dropload-noData">已经到底啦~(>_<)~~</div>'
        },
        loadDownFn: function(me) {
            // 加载按默认和最新发布排序的数据
            request({
                url:'/api/room/book_filter',
                type:'post',
                dataType:'json',
                data:{
                    'page':page,
                    'venue':venue,
                    'address':area,
                    'status':status,
                    'len':10,
                    'start_time' : searchStartTime,
                    'end_time' : searchEndTime
                },
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
                            $("#lib").append(myTemplate(res.data.list));
                            dropload.resetload();

                            $('.cancelApply').on('click' , function () {
                               var id = $(this).attr('data-id');
                                cancelApply(id , this);
                                // queren(cancelApply, id , this , "是否取消预约");
                            });
                        }

                        if (res.code == 10005) {
                            alert('请登录账户' , "/wx/login/login");
                        }
                }
            },true);
        }
    })

    function filter() {
        $.ajax({
            url:'/api/opinion/getVenueWithArea',
            type:'post',
            dataType:'json',
            success:function (result) {
                if(result.status ==1){
                    var venue1= result.data.venue;
                    var area1 = result.data.area;
                    var perform_type1 = result.data.perform_type;
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

                    var  area_html = "<li onclick='Categorytw(this)'  data-area='0'>全部</li>";
                    $.each(area1,function (i,v) {
                        area_html+='<li onclick="Categorytw(this)" data-area='+v.id+'>'+v.name+'</li>';
                    });
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
                        });
                        $("#Categoryt").html(area2_html);

                        //子区域点击获取id
                        $('#Categoryt').children('li').each(function () {
                            $(this).click(function () {
                                area = $(this).data('area3');
                                get_repository();
                            })
                        });

                    });


                    $('#Sort-Sort').find('li').on('click'  , function (event) {
                        status = $(this).attr('data-value');
                        console.log($(this).attr('data-value'));
                        get_repository();
                    });

                    //场馆点击获取id
                    $('#gradew').children('li').click(function(){
                        venue = $(this).data('venue');
                        get_repository();
                    })
                }
            }

        })
    }

    function get_repository() {
        page = 1;

        request({
            url:'/api/room/book_filter',
            type:'post',
            dataType:'json',
            data:{
                'page':page,
                'venue':venue,
                'address':area,
                'status':status,
                'len':10,
                'start_time' : searchStartTime,
                'end_time' : searchEndTime
            },
            success:function(res){

                if (res.code == 10005) {
                    alert('请登录账户');
                }

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

                $('.cancelApply').on('click' , function () {
                    var id = $(this).attr('data-id');
                    // queren(cancelApply, id , this , "是否取消预约");
                   cancelApply(id , this);
                  //  alert(1231);
                    //queren(cancel2);
                });



            }
        },true);

    }

    function cancel2(applyId ) {
        request({
            url:'/api/room/cancel',
            type:'post',
            dataType:'json',
            data:{
                'id':applyId
            },
            success:function(res){
                if (res.code == 10005) {
                    alert('请登录账户');
                }
                if(res.status ==1 ){
                    disloagInfo("删除成功");
                } else {
                    disloagInfo("删除失败");
                }
            }
        },true);
    }

    function cancelApply(applyId , that) {


        queren(function(applyId, dom){
            request({
                url:'/api/room/cancel',
                type:'post',
                dataType:'json',
                data:{
                    'id':applyId
                },
                success:function(res){
                    if (res.code == 10005) {
                        layer.open({
                            title: [
                                '提示',
                                'font-size:0.21rem'
                            ],
                            yes: function (index, layero) {
                                window.location.href = updateUrl('/wx/login/login');
                                layer.closeAll();
                            },
                            content:"请登录账户",
                            btn: '确定',
                            style: 'font-size:0.18rem;border-radius:0.18rem;height:23%'
                        });
                    }
                    if(res.status == 1 ){
                        layer.open({
                            title: [
                                '提示',
                                'font-size:0.21rem'
                            ],
                            yes: function (index, layero) {
                                window.location.href=updateUrl(window.location.href);
                                layer.closeAll();
                            },
                            content:"取消预约成功",
                            btn: '确定',
                            style: 'font-size:0.18rem;border-radius:0.18rem;height:23%'
                        });
                    } else {
                        noLogin(res.code, res.msg);
                        // alert('取消预约失败');
                    }
                }
            },true);
        },applyId, this, '是否要取消预约');
    }

    function disloagInfo(msg) {
        layui.use('layer', function() {
            layer.open({
                title: '提示',
                content: msg,
                btn: ['确认'],
                yes: function(index, layero) {
                    layer.closeAll();
                    document.getElementById("refresh").click();
                },
                btn2: function(index, layero) {
                    layer.closeAll();
                },
                cancel: function() {
                    layer.closeAll();
                    document.getElementById("refresh").click();
                },
                btnAlign: 'c',
                anim: 1,
                shade: 0.3,
                scrollbar: false
            });
        });
    }

    function updateUrl(url,key){
        var key= (key || 't') +'=';  //默认是"t"
        var reg=new RegExp(key+'\\d+');  //正则：t=1472286066028
        var timestamp=+new Date();
        if(url.indexOf(key)>-1){ //有时间戳，直接更新
            return url.replace(reg,key+timestamp);
        }else{  //没有时间戳，加上时间戳
            if(url.indexOf('\?')>-1){
                var urlArr=url.split('\?');
                if(urlArr[1]){
                    return urlArr[0]+'?'+key+timestamp+'&'+urlArr[1];
                }else{
                    return urlArr[0]+'?'+key+timestamp;
                }
            }else{
                if(url.indexOf('#')>-1){
                    return url.split('#')[0]+'?'+key+timestamp+location.hash;
                }else{
                    return url+'?'+key+timestamp;
                }
            }
        }
    }
});

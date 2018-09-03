$(function () {
    $.date('#date2');
    $.date('#date3');
    //报名状态 1 已完成 1 已报名
    var status = 0;
    var start_time='';
    var end_time='';
    var page=1;
    var len =5;

    // $(".baoming").eq(0).css('font-weight','bold');
    $(".baoming").eq(0).css('color','#1663DB');
    $(".baoming").eq(1).css('color','#141414');

    var tabLoadEnd = false;

    var dropload = $('.sources').dropload({
        scrollArea: window,
        domDown:{
            domNoData : '<div class="dropload-noData">已经到底啦~(>_<)~~</div>'
        },
        loadDownFn: function(me) {
            // 加载按默认和最新发布排序的数据
            request({
                url:'/api/activity/myact',
                type:'post',
                dataType:'json',
                data:{
                    'page':page,
                    'status':status,
                    'len':len,
                    'start_time' : start_time,
                    'end_time' : end_time
                },
                success:function(res){

                    if (res.code == 10005) {
                        alert('请登录账户' , "/wx/login/login");
                    }

                    if(res.status==1){
                        page++;
                        if (res.data.list.length < 5) {
                            // 再往下已经没有数据
                            tabLoadEnd = true;
                            // 锁定
                            dropload.lock();
                            // 显示无数据
                            dropload.noData();
                        }else{
                            dropload.unlock();
                            dropload.noData(false);
                        }

                        for(var i = 0;i<res.data.list.length;i++){
                            res.data.list[i].baoming_time = timestampToTime(res.data.list[i].baoming_time);
                        }
                        var myTemplate = Handlebars.compile($("#list").html());
                        $("#lib").append(myTemplate(res.data.list));
                        dropload.resetload();
                        // $('.child2')
                        console.log($('.child2'))




                        $('.cancelApply').on('click' , function () {
                            var id = $(this).attr('data-id');
                            var cur_obj=$(this);
                            queren(cancelApply,id,cur_obj);

                        });
                    }
                }
            },true);
        }
    })
    //我的报名状态切换
    $(".baoming").click(function () {
        page =1;
        status = $(this).index()==0 ? 2 : 1;
        start_time=  $.date('#date2');
        end_time= $.date('#date3');
        // 解锁
        dropload.unlock();
        dropload.noData(false);

        $(".baoming").css('color','#141414');
        $(this).css('color','#1663DB');
        get_myactivity();
    });

    $('.navigation li').on('click' , function () {
        status = $(this).attr('data-val')
        page = 1;
        get_myactivity()
    });

    //检索我的活动
    $("#search").click(function () {
        page =1;
        start_time=$('#date2').val();
        end_time=$('#date3').val();

        if (start_time == 0 || end_time == 0) {
            alert("请选择搜索时间");
            return;
        }

        start_time= Date.parse(new Date(start_time) ) / 1000 - 3600 * 8;
        end_time = Date.parse(new Date(end_time)) / 1000 + 3600 * 16 ;

        // 解锁
        dropload.unlock();
        dropload.noData(false);

        get_myactivity();
    });
  
    function get_myactivity() {
        request({
            url:'/api/activity/myact',
            type:'post',
            dateType:'json',
            data:{status:status,page:page,len:len},
            success:function (result) {
                page++;
                if(result.status ==1){
                    if (result.data.list.length < 5) {
                        // 再往下已经没有数据
                        tabLoadEnd = true;
                        // 锁定
                        dropload.lock();
                        // 显示无数据
                        dropload.noData();
                    }
                    for(var i = 0; i < result.data.list.length;i++){
                        result.data.list[i].baoming_time = timestampToTime(result.data.list[i].baoming_time)
                    }
                    $("#lib").html('');
                    var template=Handlebars.compile($('#list').html());
                    $("#lib").append(template(result.data.list));
                    dropload.resetload();
                    $('.child2').each(function(i){
                        if($('.child2').eq(i).text()=="已完成"){
                            $('.listbottom .button').eq(i).text('已完成');
                            $('.listbottom .button').eq(i).css('background','rgb(172, 172, 172)');
                        }
                    })
                    $('.cancelApply').on('click' , function () {
                        var id = $(this).attr('data-id');
                        var cur_obj=$(this);
                        $('.child2').each(function(i){
                            if($('.child2').eq(i).text()=="已报名"){
                                queren(cancelApply,id,cur_obj);
                            }
                        })
                    });
                }else{
                    $('#huodongList').html('');
                    dropload.resetload();
                }
            }
        },true);
    }

    function cancelApply(activity_id,obj) {
        request({
            url:'/api/activity/cancel',
            type:'post',
            dataType:'json',
            data:{
                'id':activity_id
            },
            success:function(res){

                if (res.code == 10005) {
                    alert('请登录账户');
                }
                if(res.status ==1 ){
                    obj.parents('.list').remove();
                    alert('取消预约成功');
                } else {
                    alert('取消预约失败');
                }
            }
        },true);
    }

    function queren(func,id,obj){
        layer.open({
            title: [
                '提示',
                'font-size:0.21rem'
            ],
            yes: function (index) {
                func(id,obj);
                layer.close(index);
            },
            no:function(){
                layer.closeAll();
            },
            content:'确认要取消吗？',
            btn: ['确认','取消'],
            style: 'font-size:0.18rem;border-radius:0.18rem;height:23%'
        });
    }

    Handlebars.registerHelper("handle", function(id, options) {
        var data = options.data.root;
        for (var i = 0; i< data.length; i++) {
            if (data[i].baoming_id == id) {
                switch (data[i].status) {
                    case 1 :
                        return  '<a class="caozuoBtn f-right cancelApplyBtn" data-id="'+data[i].baoming_id+'">取消报名</a>';
                    default:
                        return  '<a class="caozuoBtn f-right againApply">重新报名</a>';
                }
            }
        }
    });

    $('#lib').on('click' , '.againApply', function () {
        window.location.href = "/wx2/activity/index"
    });

    $('#lib').on('click' , '.cancelApplyBtn', function () {
        var cancelApplyId = $(this).attr('data-id');
        var data = $(this)
        request({
            url:'/api/activity/cancel',
            type:'post',
            dataType:'json',
            data:{
                'id':cancelApplyId
            },
            success:function(res){
                if (res.code == 10005) {
                    alert('请登录账户');
                }
                if(res.status ==1 ){
                    data.parents('.mui-media').find('.statusMsg').text('已取消');
                    data.parents('.mui-media').find('.cancelApplyBtn').text('重新报名');
                    data.parents('.mui-media').find('.cancelApplyBtn').removeClass('cancelApplyBtn').addClass('againApply');
                    alert('取消预约成功');
                } else {
                    alert('取消预约失败');
                }
            }
        },true);
    })
});
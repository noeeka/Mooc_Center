$(function() {
    selectNav(81);
    hot();
    //hotread();
    var page = 1;
    var len = 8;

    function recurite(page, len) {
        $.ajax({
            url: '/api/volunrecruit/index',
            type: 'GET',
            dataType: 'json',
            data: { page: page, len: len },
            success: function(res) {
                if (res.status == 1) {
                    var tpl = $("#recurite-template").html();
                    //预编译模板
                    var template = Handlebars.compile(tpl);
                    var time = res.data.list;
                    var nowdata = new Date().getTime() / 1000;
                    for (var i in time) {
                        // time[i].end_time_stamp = time[i].start_time;
                        time[i].start_time = data_format(time[i].start_time);
                        time[i].end_time = data_format(time[i].end_time);
                    }
                    var html = template(time);
                    //输入模板
                    $('#recurite').html(html);
                    console.log(time[2].end_time_stamp)
                    for (var i in time) {
                        if (nowdata < time[i].baoming_start_time) {
                            $('.layui-btn').eq(i).html('未开始');
                            $('.layui-btn').eq(i).removeClass('activity-active');
                            $('.layui-btn').eq(i).addClass('expire');
                        }
                        if (nowdata > time[i].baoming_end_time) {
                            $('.layui-btn').eq(i).html('报名停止');
                            $('.layui-btn').eq(i).removeClass('activity-active');
                            $('.layui-btn').eq(i).addClass('expire');
                        }
                        if (nowdata > time[i].end_time) {
                            $('.layui-btn').eq(i).html('已结束');
                            $('.layui-btn').eq(i).removeClass('activity-active');
                            $('.layui-btn').eq(i).addClass('expire');
                        }
                    }
                }
            }
        })
    }

    function hot() {
        var id = getParam('id');
        $.ajax({
            url: '/api/volunrecruit/index',
            type: 'post',
            dataType: 'json',
            data: {},
            success: function(response) {
                if (response.status == 1) {
                    dat = response.data.list.slice(0, 3);
                    var hotTpl = $("#hot-template").html();
                    var hotCmp = Handlebars.compile(hotTpl);
                    var hotFun = hotCmp(dat);
                    $('#Hot').html(hotFun);
                }
            }
        })
    }

    $.ajax({
        url: '/api/volunrecruit/index',
        type: 'GET',
        dataType: 'json',
        data: {},
        success: function(res) {
            if (res.status == 1) {
                if (res.data.count > 0) {
                    layui.use('laypage', function() {
                        var laypage = layui.laypage;
                        //执行一个laypage实例
                        laypage.render({
                            elem: 'test1', //注意，这里的 test1 是 ID，不用加 # 号
                            count: res.data.count, //数据总数，从服务端得到
                            next: '>',
                            limit: 8,
                            jump: function(obj, first) {
                                recurite(obj.curr, obj.limit);
                            }
                        });
                    });
                }

            }
        }
    })
})
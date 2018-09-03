$(function() {
    selectNav(81);
    hot();
    //hotread();

    var page = 1;
    var len = 8;

    function hot() {
        var id = getParam('id');
        $.ajax({
            url: '/api/volunarticle/index',
            type: 'post',
            dataType: 'json',
            data: {},
            success: function (response) {
                if (response.status == 1) {
                    dat = response.data.list.slice(0,3);
                    var hotTpl = $("#hot-template").html();
                    console.log(hotTpl);
                    var hotCmp = Handlebars.compile(hotTpl);
                    var hotFun = hotCmp(dat);
                    $('#Hot').html(hotFun);
                }
            }
        })
    }

    function reports(page, len) {
        $.ajax({
            url: '/api/volunarticle/index',
            type: 'GET',
            dataType: 'json',
            data: { cid: 26, page: page, len: len },
            success: function(res) {
                if (res.status == 1) {
                    var tpl = $("#reports-template").html();
                    //预编译模板
                    var template = Handlebars.compile(tpl);
                    var time = res.data.list;
                    for (var i in time) {
                        time[i].published_time = data_format(time[i].published_time);
                    }
                    var html = template(time);

                    //输入模板
                    $('#reports').html(html);
                }
            }
        })
    }
    $.ajax({
        url: '/api/volunarticle/index',
        type: 'GET',
        dataType: 'json',
        data: { cid: 26 },
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
                                reports(obj.curr, obj.limit);
                            }
                        });
                    });
                }

            }
        }
    })


})
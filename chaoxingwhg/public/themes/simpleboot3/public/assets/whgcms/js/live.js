$(function() {
    selectNav(36);
    title1();
    var height1 = $('.hot ul').height() + 57;
    $('.hot .classification').css('height', height1);
    var height2 = $('.area ul').height() + 57;
    $('.area .classification').css('height', height2);
    var height3 = $('.type ul').height() + 57;
    $('.type .classification').css('height', height3);
    $('.tab1 li').click(function() {
        $('.tab1 li').removeClass('active1');
        $(this).addClass('active1');
    });

})

var venue = 0; //场馆id
var area = 0; //区域id
var page = 1;
var typeid = 0;
$(function() {
    fiter();
    getList();


    //获取列表
    function getList() {
        $.ajax({
            "url": "/api/live_broadcast/index", //异步数据请求的地址
            "data": { venue: venue, page: page, address: area, typeid: typeid, len: 8 ,type:1}, //异步请求的数据
            "data_type": "json",
            "success": function(res) {
                if (res.status == 1) {
                    parseItem(res);
                    //分页
                    if (res.data.num > 0) {
                        layui.use('laypage', function() {
                            var laypage = layui.laypage;
                            //执行一个laypage实例
                            laypage.render({
                                elem: 'test1', //注意，这里的 test1 是 ID，不用加 # 号
                                count: res.data.num, //数据总数，从服务端得到
                                next: '>',
                                limit: 8,
                                jump: function(obj, first) {
                                    if (!first) {
                                        $.ajax({
                                            url: '/api/live_broadcast/index',
                                            dataType: 'json',
                                            type: 'post',
                                            data: { 'page': obj.curr, 'len': 8 },
                                            success: function(res) {
                                                parseItem(res);
                                            },
                                            error: function(res) {
                                                getdialog("获取直播失败");
                                            }
                                        });
                                    }
                                }
                            });
                        });
                    } else {
                        $("#test1").html('');
                    }
                    if ($('.list li').length == 0) {
                        $('.no').show();
                    } else {
                        $('.no').hide();
                    }
                }
            }, //异步请求成功后的回调函数
            "error": function(res) {
                getdialog("获取直播失败");
            } //异步请求失败后的回调函数.
        });
    }

    function parseItem(res) {
        Handlebars.registerHelper('date', function(value) {
            var date = new Date();
            date.setTime(value * 1000); //??
            var mouth = (date.getMonth() + 1) > 9 ? date.getMonth() + 1 : '0' + (date.getMonth() + 1);
            var dat = date.getDate() > 9 ? date.getDate() : '0' + date.getDate();
            var hour = date.getHours() > 9 ? date.getHours() : '0' + date.getHours();
            var minute = date.getMinutes() > 9 ? date.getMinutes() : '0' + date.getMinutes();
            return date.getFullYear() + '-' + mouth + '-' + dat + ' ' + hour + ':' + minute;

        });

        Handlebars.registerHelper('status', function(value, data) {
            var data = data.data.root[data.data.index];
            var timestamp = new Date().getTime() / 1000;
            if (timestamp < data['start_time']) {
                return "即将开始";
            } else if (timestamp > data['end_time']) {
                return "直播结束";
            } else {
                return "进行中";
            }
        });

        // 抓取模板数据
        var myTemplateScript = $("#live-template").html();
        // 编译模板
        var myTemplate = Handlebars.compile(myTemplateScript);
        $("#live_list").html(myTemplate(res.data.list));

        //为三个状态设置不同的样式
        var len = $('#live_list li').length;
        for (var i = 0; i < len; i++) {
            if ($('#live_list .layui-btn').eq(i).html() == '即将开始') {
                $('#live_list .layui-btn').eq(i).removeClass('active3');
                $('#live_list .layui-btn').eq(i).addClass('active2');
                $("#live_list>li>a").eq(i).attr("href", "#");
                $("#live_list>li>a").eq(i).attr("target", "_self");
            }
            if ($('#live_list .layui-btn').eq(i).html() == '进行中') {
                // $('#live_list .layui-btn').eq(i).removeClass('active');
                $("#live_list>li>a").eq(i).attr("href", res.data.list[i].live_link);
            }
            if ($('#live_list .layui-btn').eq(i).html() == '直播结束') {
                $('#live_list .layui-btn').eq(i).removeClass('active3');
                $('#live_list .layui-btn').eq(i).addClass('active');
                if (res.data.list[i].playback_link != "") {
                    $("#live_list>li>a").eq(i).attr("href", res.data.list[i].playback_link);
                } else {
                    $("#live_list>li>a").eq(i).attr("href", "#");
                    $("#live_list>li>a").eq(i).attr("target", "_self");
                }
            }
        }
    };

    $("#live_list").on('click', 'li', function() {
        if ($(this).find(".layui-btn").html() == "即将开始") {
            getdialog("直播还未开始");
        }
        if($(this).find("button").html() == "直播结束" && $(this).children("a").attr("href") == "#"){
            getdialog("直播已结束，暂无回放链接");
        }
    });


    //热门场馆及区域
    function fiter() {
        $.ajax({
            "url": "/api/filter",
            "data": "",
            "success": function(res) {
                if (res.status == 1) {
                    //场馆
                    // 抓取模板数据
                    var TemplateScript = $("#fiter-template").html();
                    //编译模板
                    var venueTemplate = Handlebars.compile(TemplateScript);
                    $("#venue").html(venueTemplate(res.data.venue));
                    var height1 = $('.hot ul').height() + 57;
                    $('.hot .classification').css('height', height1);
                    $('.hotvenus li').eq(0).css('margin-bottom', height1 - 61);

                    // 区域
                    // 抓取模板数据
                    var areaTemplateScript = $("#area_list").html();
                    //编译模板
                    var areaTemplate = Handlebars.compile(areaTemplateScript);
                    $("#area").html(areaTemplate(res.data.area));
                    var height2 = $('.area ul').height() + 57;
                    $('.area .classification').css('height', height2);
                    if ($('.areavenus li').eq(0).hasClass('live-active')) {
                        $('.areavenus li').eq(0).css('margin-bottom', height2 - 61);
                    }
                    var height3 = $('.type ul').height() + 57;
                    $('.type .classification').css('height', height3);
                    $('.typevenus li').eq(0).css('margin-bottom', height3 - 61);
                    //场馆筛选
                    $("#venue").children('li').each(function() {
                        $(this).click(function() {
                            $("#venue").children('li').removeClass('live-active');
                            $("#venue").children('li').removeClass('activity-active');
                            $(this).addClass('live-active');
                            $(this).addClass('activity-active');
                            venue = $(this).data('venue');
                            page = 1;
                            getList();
                        })
                    })

                    //区域筛选
                    $("#area").children('li').each(function() {
                        $(this).click(function() {
                            $("#area").children('li').removeClass('live-active');
                            $("#area").children('li').removeClass('activity-active');
                            $(this).addClass('live-active');
                            $(this).addClass('activity-active');
                            $index = $(this).index();
                            //点击二级区域
                            if ($index == 0) {
                                $(this).nextAll('ul').hide();
                            } else {
                                $(this).nextAll('ul').hide();
                                $(this).nextAll('ul').eq($index - 1).show();
                                $area_level2 = $(this).nextAll('ul').eq(($index - 1));
                                $area_level2.children('li').each(function() {
                                    $area_level2.children('li').removeClass('activity-active1');
                                    $(this).click(function() {
                                        $area_level2.children('li').removeClass('activity-active1');
                                        $(this).addClass('activity-active1')
                                        area = $(this).data('area');
                                        page = 1;
                                        getList();
                                    })
                                })
                            }
                            //区域
                            var height2 = $('.area ul').height() + 57;
                            $('.area .classification').css('height', height2);
                            area = $(this).data('area');
                            page = 1;
                            getList();
                        })
                    })

                    //类型筛选
                    $("#types").children('li').each(function() {
                        $(this).click(function() {
                            $("#types").children('li').removeClass('live-active');
                            $("#types").children('li').removeClass('activity-active');
                            $(this).addClass('live-active');
                            $(this).addClass('activity-active');
                            typeid = $(this).attr("data-type");
                            page = 1;
                            getList();
                        })
                    });

                }
            }
        });

    }


});
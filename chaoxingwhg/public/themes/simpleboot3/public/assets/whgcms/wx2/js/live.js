
$(function () {
    //设置title
    title2(36);
    var venue = 0;//场馆id
    var area = 0;//区域id
    var typeid = 0;
    var page = 1;
    var len = 6;
    fiter();

    //dropload 下拉刷新
    var dropload = $('.huodong').dropload({
        scrollArea: window,
        domDown:{
            domNoData : '<div class="dropload-noData">已经到底啦~(>_<)~~</div>'
        },
        loadDownFn: function (me) {
            $.ajax({
                url: '/api/live_broadcast',
                type: 'post',
                data: {venue: venue, address: area, typeid: typeid, page: page, len: len},
                dataType: 'json',
                success: function (res) {
                    if (res.status == 1) {
                        page++; //页数加1
                        if (res.data.list.length < len) {
                            // 再往下已经没有数据
                            // 锁定
                            me.lock();
                            // 显示无数据
                            me.noData();
                        } else {
                            me.unlock();
                            me.noData(false);
                        }
                        parseItem(res, 0);
                    }
                }
            });

        }
    });

    //获取列表
    function getList() {
        page = 1;
        $.ajax({
            "url": "/api/live_broadcast/index",
            "data": {venue: venue, address: area, typeid: typeid, page: page, len: len},
            "data-type": "json",
            success: function (res) {
                if (res.status == 1) {
                    page++;
                    dropload.unlock();
                    dropload.noData(false);
                    parseItem(res, 1);
                }
            }
        });
    }

    //模板
    function parseItem(res, state) {
        //handlebars注册 helper
        Handlebars.registerHelper('date', function (value) {
            var date = new Date();
            date.setTime(value * 1000);
            var mouth = (date.getMonth() + 1) > 9 ? date.getMonth() + 1 : '0' + (date.getMonth() + 1);
            var day = date.getDay() > 9 ? date.getDay() : '0' + date.getDay();

            return date.getFullYear() + '-' + mouth + '-' + day;
        });
        Handlebars.registerHelper('status', function (value, data) {
            var date = new Date();
            var timestamp = date.getTime() / 1000;
            var time = data.data.root[data.data.index];
            if (timestamp < time['start_time']) {
                return "即将开始";
            } else if (timestamp > time['end_time']) {
                return "直播结束";
            } else {
                return "直播中";
            }
        });
        // 抓取模板数据
        var long = res.data.list.length;
        for (var j = 0; j<long;j++){
            res.data.list[j].page = page;
        }
        var myTemplateScript = $("#live_template").html();
        var myTemplate = Handlebars.compile(myTemplateScript);

        if (state == 1) {
            $("#live").html(myTemplate(res.data.list));
        } else {
            $("#live").append(myTemplate(res.data.list));
        }

        //为三个状态设置不同的链接
        var length = $('#live  .page'+page).length;
        for (var i = 0; i < length; i++) {
            if ($('#live .page'+page+' .liveState').eq(i).html() == '即将开始') {
                $("#live>.page"+page+">a").eq(i).attr("href", "javascript:;");
                //$("#live>.page"+page+">a").eq(i).attr("target", "_self");
            }
            if ($('#live .page'+page+' .liveState').eq(i).html() == '直播中') {
                $("#live>.page"+page+">a").eq(i).attr("href", "/wx/Readiframe/index?srcid=" + Base64.encodeURI(res.data.list[i].wx_live_link) + "&title=文化直播");
            }
            if ($('#live .page'+page+' .liveState').eq(i).html() == '直播结束') {
                if (res.data.list[i].wx_playback_link != '') {
                    $("#live>.page"+page+">a").eq(i).attr("href", "/wx/Readiframe/index?srcid=" + Base64.encodeURI(res.data.list[i].wx_playback_link) + "&title=文化直播");
                } else {
                    $("#live>.page"+page+">a").eq(i).attr("href", "javascript:;");
                    //$("#live>.page"+page+">a").eq(i).attr("target", "_self");
                }
            }
        }

        dropload.resetload();
    }

    $("#live").on('click', 'li', function () {
        if ($(this).find(".liveState").html() == "即将开始") {
            alert("直播还未开始");
        }
        if ($(this).find(".liveState").html() == "直播结束" && $(this).children("a").attr("href") == "javascript:;") {
            alert("直播已结束，暂无回放链接");
        }
    });

//获取热门场馆、区域
    function fiter() {
        $.ajax({
            "url": "/api/fiter",
            "date": "",
            success: function (res) {
                if (res.status == 1) {
                    //场馆
                    var venueTemplateScript = $("#venue-template").html();
                    var venueTemplate = Handlebars.compile(venueTemplateScript);
                    $("#gradew").html(venueTemplate(res.data.venue));

                    //区域
                    var areaTemplateScript = $("#area-template").html();
                    var areaTemplate = Handlebars.compile(areaTemplateScript);
                    $("#Categorytw").html(areaTemplate(res.data.area));
                    $("#Categorytw").children('li').each(function () {
                        $(this).click(function () {
                            var index = $(this).index() - 1;
                            if (index == -1) {
                                //点击全部
                                var parent = {id: 0, name: "全部", son: []}
                                var sonTemplateScript = $("#son-template").html();
                                var sonTemplate = Handlebars.compile(sonTemplateScript);
                                $("#Categoryt").html(sonTemplate(parent));
                            } else {
                                //点击其他区域 获取此区域以及此区域子集
                                var parent = res.data.area[index];
                                var sonTemplateScript = $("#son-template").html();
                                // 编译模板
                                var sonTemplate = Handlebars.compile(sonTemplateScript);
                                $("#Categoryt").html(sonTemplate(parent));
                            }
                        })
                    });

                    //场馆筛选
                    $("#gradew").children('li').each(function () {
                        $(this).click(function () {
                            venue = $(this).data('venue');
                            getList();
                        })
                    });

                    //区域筛选
                    $("#Categorytw").children('li').each(function () {
                        $(this).click(function () {
                            var index = $(this).index();
                            if (index == 0) {
                                area = $(this).data('area');
                                getList();
                            } else {
                                $("#Categorytw").next('ul').children('li').each(function () {
                                    $(this).click(function () {
                                        area = $(this).data('area');
                                        getList();
                                    });
                                })
                            }
                        });
                    });

                    //状态筛选
                    $("#Sort-Sort").children('li').each(function () {
                        $(this).click(function () {
                            var index = $(this).index();
                            if (index == 0) {
                                typeid = $(this).data('type');
                                getList();
                            } else {
                                typeid = $(this).data('type');
                                getList();
                            }
                        });
                    });
                }
            },
            error: function (res) {
                alert("获取筛选条件失败");
            }
        })

    }
});


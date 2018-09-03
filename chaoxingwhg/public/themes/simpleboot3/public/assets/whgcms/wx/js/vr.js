
$(function () {
    var venue = 0;//场馆id
    var area = 0;//区域id
    var page = 1;
    var len = 10;
    fiter();

    //dropload 下拉刷新
    var dropload = $('.huodong').dropload({
        scrollArea: window,
        domDown:{
            domNoData : '<div class="dropload-noData">已经到底啦~(>_<)~~</div>'
        },
        loadDownFn: function (me) {
            $.ajax({
                url: '/api/article/index',
                type: 'post',
                data: {
                venue: venue,
                area: area,
                cid: 18,
                page: page,
                len: len
                },
                dataType: 'json',
                success: function (res) {
                    // console.log(res)
                    if (res.status == 1) {
                        page++; //页数加1
                        // console.log(len)
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
            "url": "/api/article/index",
            "data": {
                venue: venue,
                area: area,
                cid: 18,
                page: page,
                len: len
            },
            "data-type": "json",
            success: function (res) {
                console.log(res)
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
            var hour = date.getHours() > 9 ? date.getHours() : '0' + date.getHours();
            var minute = date.getMinutes() > 9 ? date.getMinutes() : '0' + date.getMinutes();

            return date.getFullYear() + '-' + mouth + '-' + day + ' ' + hour + ':' + minute;
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
        var myTemplateScript = $("#live_template").html();
        var myTemplate = Handlebars.compile(myTemplateScript);
        if (state == 1) {
            $("#live").html(myTemplate(res.data.list));
        } else {
            $("#live").append(myTemplate(res.data.list));
        }

        //为三个状态设置不同的链接
        var len = $('#live li').length;
        for (var i = 0; i < len; i++) {
            if ($('#live .liveState').eq(i).html() == '即将开始') {
                $("#live>li>a").eq(i).attr("href", "#");
            }
            if ($('#live .liveState').eq(i).html() == '直播中') {
                $("#live>li>a").eq(i).attr("href", "/wx/Readiframe/index?srcid=" + res.data.list[i].wx_live_link + "&title=文化直播");
            }
            if ($('#live  .clearfix .liveState').eq(i).html() == '直播结束') {
                if (res.data.list[i].wx_playback_link != null) {
                    $("#live>li>a").eq(i).attr("href", "/wx/Readiframe/index?srcid=" + res.data.list[i].wx_playback_link + "&title=文化直播");
                } else {
                    $("#live>li>a").eq(i).attr("href", "#");
                }
            }
        }

        dropload.resetload();
    }


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
                    // $("#Sort-Sort").children('li').each(function () {
                    //     $(this).click(function () {
                    //         var index = $(this).index();
                    //         if (index == 0) {
                    //             typeid = $(this).data('type');
                    //             getList();
                    //         } else {
                    //             typeid = $(this).data('type');
                    //             getList();
                    //         }
                    //     });
                    // });
                }
            },
            error: function (res) {
                alert("获取筛选条件失败");
            }
        })

    }
});


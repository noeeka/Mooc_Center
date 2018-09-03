$(function () {
    //设置title
    title2(17);
    //默认和最新分页
    var pg = 1;
    //场馆
    var venue = 0;
    //区域
    var area = 0;
    //类型
    var typeid = 0;
    //1:默认和最新排序，2：人气排序
    var order = 'publish_time';
    var len = 10;

    var itemIndex = 1;
    var tab1LoadEnd = false;


    var template = Handlebars.compile($("#venue_list").html());

    var dropload = $('.sources').dropload({
        scrollArea: window,
        domDown:{
            domNoData : '<div class="dropload-noData">已经到底啦~(>_<)~~</div>'
        },
        loadDownFn: function (me) {
            if (itemIndex == 1 || itemIndex == 2) {
                // 加载按默认和最新发布排序的数据
                $.ajax({
                    url: '/api/room/',
                    type: 'post',
                    data: {page: pg, venue: venue, area: area, typeid: typeid, sort: order, len: len},
                    dataType: 'json',
                    success: function (result) {
                        if (result.status == 1) {
                            pg++; //页数加1
                            if (result.data.list.length < 6) {
                                // 再往下已经没有数据
                                tab1LoadEnd = true;
                                // 锁定
                                me.lock();
                                // 显示无数据
                                me.noData();
                            }
                            $(".sourcesList").append(template(result.data.list))
                            me.resetload();
                        }
                    }
                })
            }
        }
    })

    //获取场馆，区域
    filter(dropload, template);
    //获取场馆类型
    get_room_type();
    //切换排序
    $('.screenTj').children('li').each(function () {
        $(this).click(function () {
            // if ($(this).index() != 0) {
                $('.screenTj').children('li').removeClass('active');
                $(this).addClass('active');
                tab1LoadEnd = false;
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
                pg = 1;
                if ($(this).index() == 2) {
                    order = 1;
                } else {
                    order = 0;
                }
                get_data(dropload, template);
            // }
        })
    })

    //获取活动
    function get_data(dropload, template) {
        $.ajax({
            url: '/api/room/index',
            type: 'post',
            data: {page: pg, venue: venue, area: area, typeid: typeid, sort: order, len: len},
            dataType: 'json',
            success: function (result) {

                if (result.status == 1) {
                    pg++; //页数加1
                    if (result.data.list.length < 6) {
                        // 再往下已经没有数据
                        tab1LoadEnd = true;
                        // 锁定
                        dropload.lock();
                        // 显示无数据
                        dropload.noData();
                    }
                    $(".sourcesList").html("");
                    //渲染数据
                    $(".sourcesList").append(template(result.data.list))
                    dropload.resetload();
                } else {
                    $('#huodongList').html('');
                    dropload.resetload();
                }
            }
        })
    }

    //获取场馆，区域
    function filter(dropload, template) {

        $.ajax({
            url: '/api/filter',
            type: 'post',
            dataType: 'json',
            async: false,
            success: function (result) {
                if (result.status == 1) {
                    var data_venue = result.data.venue;
                    var data_area = result.data.area;
                    var date_activity_type = result.data.activity_type;

                    //热门场馆
                    $("#gradew").html("");
                    var venue_html = '<li data-venue="0" onclick="grade1(this)">全部</li>';
                    $.each(data_venue, function (i, v) {
                        venue_html += '<li onclick="grade1(this)" data-venue=' + v.id + '>' + v.name + '</li>';
                    })
                    $("#gradew").html(venue_html);

                    $("#gradew").children('li').each(function () {
                        $(this).click(function () {
                            tab1LoadEnd = false;
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
                            pg = 1;
                            venue = $(this).data('venue');
                            get_data(dropload, template);
                        })
                    })

                    //区域
                    $("#Categorytw").html("");
                    var area_html = '<li onclick="Categorytw(this)" data-area="0">全部</li>';
                    var area2_html = "";

                    $.each(data_area, function (i, v) {
                        area_html += '<li onclick="Categorytw(this)" data-area=' + v.id + ' data-son=' + JSON.stringify(v.son) + '>' + v.name + '</li>';
                    })
                    $("#Categorytw").html(area_html);

                    $("#Categorytw").find('li').each(function () {
                        $(this).click(function () {
                            $(".Category-t").html("");
                            area2_html = '<li onclick="Categoryt(this)" data-area1="' + $(this).data('area') + '">' + $(this).html() + '</li>';
                            var son = $(this).data('son');
                            for (var i in son) {
                                area2_html += '<li onclick="Categoryt(this)" data-area1=' + son[i].id + '>' + son[i].name + '</li>';
                            }
                            $(".Category-t").html(area2_html);

                            //二级区域
                            $(".Category-t").children('li').each(function () {
                                $(this).click(function () {
                                    pg = 1;
                                    area = $(this).data('area1');
                                    get_data(dropload, template);
                                })
                            })
                        })
                    })

                }
            }
        })
    }
    
    function get_room_type() {
        $.ajax({
            url: '/api/room/type',
            type: 'post',
            dataType: 'json',
            async: false,
            success:function (reslut) {
                $("#Sort-Sort").html("");
                var room_type_html = '<li data-type="0" onclick="Sorts(this)">全部</li>';
                $.each(reslut.data, function (i, v) {
                    room_type_html += '<li onclick="Sorts(this)" data-type=' + v.id + '>' + v.name + '</li>';
                })
                $("#Sort-Sort").html(room_type_html);
                $("#Sort-Sort").children('li').each(function () {
                    $(this).click(function () {
                        pg = 1;
                        typeid = $(this).data('type');
                        get_data(dropload, template);
                    })
                })
            }
        })
    }

})





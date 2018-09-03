$(function() {
    selectNav(19);
    title1();
    Handlebars.registerHelper('date', function(value) {
        var date = new Date();
        date.setTime(value * 1000);
        return date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();
    });
    Handlebars.registerHelper('image', function(value) {
        if (value == '') {
            return '/upload/portal/2.png';
        } else {
            return value;
        }
    });

    function refreshHeight(className, init) {
        var height = $('.' + className + ' ul').height() + 57;
        $('.' + className + ' .classification').css('height', height);
        if (className == 'hot' || className == 'type') {
            $('.' + className + ' li:first-child').css('margin-bottom', height - 61);
        } else {
            if (init === true) {
                $('.area li:first-child').css('margin-bottom', height - 61);
            }
            // if(!$('.area li:first-child').hasClass('type-change')){
            //     $('.area li:first-child').css('height', height-57);
            // }
        }
    }

    $('.tab1 li').click(function() {
        $('.tab1 li').removeClass('active1');
        // var index1 = $(this).attr('data-index');
        // $('.tab1 li').eq(index1).addClass('active1');
        $(this).addClass('active1');
        sort = $(this).data('index') == 5 ? 'hot' : 'time';
        getData();
    });
    //获取初始化参数
    var vid = 0;
    var aid = 0;
    var tid = 0;
    var sort = 0;
    var page = 1;
    var limit = 8;
    var area = null;
    //筛选条件
    $.ajax({
        url: '/api/filter/index',
        dataType: 'json',
        async: false,
        success: function(res) {
            if (res.status == 1) {
                renderVenue(res);
                area = res.data.area;
                initArea();
                getData();
            }
        }
    });
    //筛选器选中状态
    $('.hot ul').on('click', 'li', function() {
        $('.hot li').removeClass('venue-active');
        $('.hot li').removeClass('activity-active ');
        $(this).addClass('venue-active');
        $(this).addClass('activity-active ');
        getData();
    });
    $('.area').on('click', '.level>li', function() {
        $('.area .level>li').removeClass('area-active');
        $('.area .level>li').removeClass('activity-active ');
        $(this).addClass('area-active');
        $(this).addClass('activity-active');
        renderChild($(this).data('id'));
        // refreshHeight();
        getData();
    });
    $('.area').on('click', '.level2>li', function() {
        $('.area .level2>li').removeClass('area-child-active');
        $('.area .level2>li').removeClass('activity-active1');
        $(this).addClass('area-child-active');
        $(this).addClass('activity-active1');
        getData();
    });

    //获取数据
    function getData() {
        page = 1;
        ajax(function(res) {
            if (res.status == 1) {
                var tpl = $('#item').html();
                var template = Handlebars.compile(tpl);
                $('.main-list .list').html(template(res.data.list));
                if ($('.list li').length == 0) {
                    $('.no').show();
                } else {
                    $('.no').hide();
                }
                layui.use('laypage', function() {
                    var laypage = layui.laypage;
                    //执行一个laypage实例
                    if (res.data.num > 0) {
                        laypage.render({
                            elem: 'test1', //注意，这里的 test1 是 ID，不用加 # 号
                            count: res.data.num, //数据总数，从服务端得到
                            next: '>',
                            limit: limit,
                            jump: function(obj, first) {
                                if (!first) {
                                    page = obj.curr;
                                    ajax(function(res) {
                                        if (res.status == 1) {
                                            var tpl = $('#item').html();
                                            var template = Handlebars.compile(tpl);
                                            $('.main-list .list').html(template(res.data.list));
                                        }
                                    });
                                }
                            }
                        });
                    } else {
                        $('#test1').html('');
                    }
                });
            } else {
                getdialog('获取列表失败');
            }
        });
    }

    function ajax(fun) {
        var venue_id = $('.venue-active').data('id');
        var area_id = $('.area-active').data('id');
        area_id = $('.area-child-active').data('id') || area_id;
        $.ajax({
            url: '/api/article/index',
            dataType: 'json',
            data: {
                venue: venue_id,
                area: area_id,
                cid: 19,
                page: page,
                len: limit,
                sort: sort
            },
            async: true,
            success: fun,
            error: function(res) {
                console.log('ajax error');
            }
        });
    }

    function renderVenue(res) {
        var hot = vid == 0 ? '<li class="venue-active activity-active" data-id="0">全部</li>' : '<li data-id="0">全部</li>';
        //热门场馆
        for (var i in res.data.venue) {
            var v = res.data.venue[i];
            if (v['id'] == vid) {
                hot += '<li class="venue-active activity-active" data-id="' + v['id'] + '">' + v['name'] + '</li>';
            } else {
                hot += '<li data-id="' + v['id'] + '">' + v['name'] + '</li>';
            }
        }
        $('.hot ul').html(hot);
        refreshHeight('hot');
    }

    function initArea() {
        var areaHtml = '<li class="area-active activity-active" data-id="0">全部</li>';
        for (var j in area) {
            var v = area[j];
            areaHtml += '<li data-id="' + v['id'] + '">' + v['name'] + '</li>';
        }
        $('.area ul').html(areaHtml);
        refreshHeight('area', true);
    }

    function renderChild(pid) {
        var areaHtml = '';
        for (var i in area) {
            if (pid == area[i]['id']) {
                areaHtml += '<ul class="level2 clearfix">';
                for (var j in area[i]['son']) {
                    var v = area[i]['son'][j];
                    areaHtml += '<li data-id="' + v['id'] + '">' + v['name'] + '</li>';
                }
                areaHtml += '</ul>';
                break;
            }
        }
        $('.area .level>ul').remove();
        $('.area .level').append(areaHtml);
        refreshHeight('area');
    }
})
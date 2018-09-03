$(function() {
    selectNav(17);
    //变量初始化
    var id = getParam('id', 0); //room id
    var bookList = []; //已预约时间戳列表
    var weeks = ['日', '一', '二', '三', '四', '五', '六']; //星期字符串数组
    var statusStrArr = { //状态数组
        'disabled': '<td><span style="padding: 5px 15px;" class="expire">无法预约</span></td>',
        'haveBook': '<td><span style="padding: 5px 20px;" class="expire">已预约</span></td>',
        'otherBook': '<td><span style="padding: 5px 20px;" class="expire">被预约</span></td>'
    };

    //点击场馆预约和介绍按钮变换
    nowActive('.changguan .title span', 'zhutise');

    //tab页切换
    $('.yuyuebtn').click(function() {
        $('.venuesyuyue').show();
        $('.venuesjieshao').hide();
        $('.venuesxiangqing').hide();
    });
    $('.jieshaobtn').click(function() {
        $('.venuesyuyue').hide();
        $('.venuesxiangqing').hide();
        $('.venuesjieshao').show();
    });
    $('.xiangqingbtn').click(function() {
        $('.venuesxiangqing').show();
        $('.venuesyuyue').hide();
        $('.venuesjieshao').hide();
    });


    //获取场馆信息
    $.ajax({
        url: '/api/room/read',
        data: { id: id },
        dataType: 'json',
        success: function(res) {
            if (res.status == 1) {
                //渲染基本信息
                renderInfo(res);
                //设置时间段
                renderTable(res.data);
                $('title').html(res.data.name);
            }
        },
        error: function() {
            console.log('ajax error');
        }
    });

    //点击右箭头
    $('.rightbtn img').click(function() {
        // console.log($(".yuyueContent").css('left'))
        if ($(".yuyueContent").css('left') == '-2142px') {
            // console.log(1470)
            return false;
        }
        if ($('.yuyueContent').is(":animated")) {
            return false
        }
        $(".yuyueContent").animate({
            left: "-=102px"
        }, 500);

    });

    //点击左箭头
    $('.leftbtn img').click(function() {
        if ($(".yuyueContent").css('left') == '0px') {
            // console.log(0)
            return false;
        }
        if ($('.yuyueContent').is(":animated")) {
            return false
        }
        $(".yuyueContent").animate({
            left: "+=102px"
        }, 500);
    });

    //点击预约按钮
    $('#table').on('click', '.bookBtn', function() {
        var timestamp = $(this).data('timestamp');
        var that = this;
        request({
            url: '/api/room/book',
            type: 'post',
            data: { room_id: id, 'start_time': timestamp },
            dataType: 'json',
            success: function(res) {
                if (res.status == 1) {
                    $(that).parent().html('<span style="padding: 5px 20px;" class="expire">已预约</span>');
                } else {
                    noLogin(res, false);
                }
            },
            error: function() {
                console.log('ajax error');
            }
        }, true);
    });

    //判断是否已预约
    function HasBook(timestamp) {
        if (bookList != -1 && bookList.length == 0) {
            request({
                url: '/api/room/read_book',
                data: { room_id: id },
                dataType: 'json',
                async: false,
                type: 'post',
                success: function(res) {
                    if (res.status == 1 && res.data.num > 0) {
                        bookList = res.data;
                    } else {
                        bookList = -1;
                    }
                },
                error: function() {
                    console.log('ajax error');
                }
            }, true);
        }
        if (bookList == -1) {
            return false;
        } else {
            if (bookList.my.indexOf(timestamp) != -1) {
                return 1;
            } else if (bookList.other.indexOf(timestamp) != -1) {
                return 2;
            } else {
                return false;
            }
        }
    }

    //渲染基本信息
    function renderInfo(res) {
        var tpl = $('#venuesinfo').html();
        var template = Handlebars.compile(tpl);
        $('.venuesinfo').html(template(res.data));
        $('#open_date').html(genOpenDate(res.data));
        $('#open_start').html(genHour(res.data.open_start_time_am) + '～' + genHour(res.data.open_end_time_am));
        $('#open_end').html(genHour(res.data.open_start_time_pm) + '～' + genHour(res.data.open_end_time_pm));
        $('.venuesjieshao p').html(res.data.abstract);
        $('.changuanjieshao p').html(res.data.abstract);
        $('.venuesxiangqing li').eq(0).children('span').html(res.data.area);
        $('.venuesxiangqing li').eq(1).children('span').html(res.data.audio);
        $('.xiangqing li').eq(0).children('span').html(res.data.area);
        $('.xiangqing li').eq(1).children('span').html(res.data.audio);
    }

    //获取时间
    function genHour(hur) {
        return hur > 9 ? hur + ':00' : '0' + hur + ':00';
    }

    //获取开放日期字符串
    function genOpenDate(data, flag) {
        if (data.preset_time == 0) {
            return flag == true ? [0, 1, 2, 3, 4, 5, 6] : '每天';
        } else if (data.preset_time == 1) {
            return flag == true ? [1, 2, 3, 4, 5] : '周一到周五';
        } else {
            if (flag == true) {
                return data.custom_preset_time.split(',').sort().map(function(v) {
                    return parseInt(v);
                });
            } else {
                return '周' + data.custom_preset_time.split(',').sort().map(function(v) {
                    return weeks[v];
                }).join('、');
            }
        }
    }

    //添加当前状态
    function nowActive(dom, domClass) {
        $(dom).click(function() {
            $(this).siblings().removeClass(domClass);
            $(this).parents('ul').find('a').removeClass(domClass);
            $(this).addClass(domClass);
        })
    }

    //获取第n天的标题字符串
    function genIncDateStr(i) {
        var today = new Date;
        today.setTime(today.getTime() + 86400000 * i);
        return '<p class="week-th">周' + weeks[today.getDay()] + '</p><p class="date-th">' + today.getFullYear() + '/' + (today.getMonth() + 1) + '/' + today.getDate() + '</p>';
    }

    //通过距当前日期的天数和时间生成对应的时间戳
    function genTimestamp(n, hour) {
        var today = new Date;
        today.setTime(today.getTime() + 86400000 * n);
        today.setHours(hour);
        today.setMinutes(0);
        today.setSeconds(0);
        return Math.floor(today.getTime() / 1000);
    }

    //渲染th
    function renderTh() {
        var html = '<tr>';
        for (var n = 0; n < 28; n++) {
            html += '<th>' + genIncDateStr(n) + '</th>';
        }
        $('#table').html(html + '</tr>');
    }

    //渲染左侧时间li
    function renderLi(data) {
        var am = '';
        for (var i = data.open_start_time_am; i <= data.open_end_time_am; i++) {
            am += '<li>' + genHour(i) + '</li>';
        }
        $('.am').html(am);
        var pm = '';
        for (var i = data.open_start_time_pm; i <= data.open_end_time_pm; i++) {
            pm += '<li>' + genHour(i) + '</li>';
        }
        $('.pm').html(pm);
    }

    //渲染预约模块
    function renderTable(data) {
        renderTh();
        renderLi(data);
        renderTbody(data);
    }

    //渲染tbody
    function renderTbody(data) {
        var html = '';
        for (var hour = data.open_start_time_am; hour < data.open_end_time_am; hour++) {
            html += '<tr>';
            for (var n = 0; n < 28; n++) {
                html += genTdStr(n, hour, data);
            }
            html += '</tr>';
        }
        html += '<tr style="height: 60px;"></tr>';
        for (var hour = data.open_start_time_pm; hour < data.open_end_time_pm; hour++) {
            html += '<tr>';
            for (var n = 0; n < 28; n++) {
                html += genTdStr(n, hour, data);
            }
            html += '</tr>';
        }
        $('#table').append(html);
    }

    //获取每一个td的html字符串
    function genTdStr(n, hour, data) {
        var timestamp = genTimestamp(n, hour);
        var now = Math.floor((new Date).getTime() / 1000);
        if (timestamp < now) {
            return statusStrArr['disabled'];
        } else {
            var hasBook = HasBook(timestamp);
            if (hasBook !== false) {
                return hasBook == 1 ? statusStrArr['haveBook'] : statusStrArr['otherBook'];
            } else {
                if (canBook(timestamp, data)) {
                    return '<td><span class="bookBtn activity-active" data-timestamp="' + timestamp + '">预约</span></td>';
                } else {
                    return statusStrArr['disabled'];
                }
            }
        }
    }

    //是否可以预约
    function canBook(timestamp, data) {
        var date = new Date();
        date.setTime(timestamp * 1000);
        var weekArr = genOpenDate(data, true);
        if (weekArr.indexOf(date.getDay()) == -1) {
            return false;
        } else {
            return true;
        }
    }
    hot();

    function hot() {
        var id = getParam('id');
        console.log(id)
        $.ajax({
            url: '/api/room/index?venue=0&area=0&typeid=0&page=1&len=3&type=1&sort=0',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status == 1) {
                    var hotTpl = $("#hot-template").html();
                    var hotCmp = Handlebars.compile(hotTpl);
                    var hotFun = hotCmp(response.data);
                    $('#Hot').html(hotFun);
                }
            }
        })
    }
    // 地图
    new BaiduMap({
        id: "map",
        title: {
            text: "北京故宫博物院",
            className: "title" // 选填--样式类名
        },
        content: {
            className: "content", // 选填--样式类名
            text: ["地址：北京市东城区东华门大街"]
        },
        point: {
            lng: "116.412222",
            lat: "39.912345"
        },
        level: 15, //  选填--地图级别--(默认15)
        zoom: true, // 选填--是否启用鼠标滚轮缩放功能--(默认false)
        type: ["地图", "卫星", "三维"], // 选填--显示地图类型--(默认不显示)

        icon: { // 选填--自定义icon图标
            url: "/themes/simpleboot3/public/assets/whgcms/images/venues/icon.png",
            width: 36,
            height: 36
        }
    });
});
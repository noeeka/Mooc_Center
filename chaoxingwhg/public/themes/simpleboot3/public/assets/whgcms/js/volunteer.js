$(function() {
    selectNav(81);
    title1();

    /*获取url*/
    var nid = getUrlVal(location.href, 'nid', 0);
    //活动报道更多url
    var report_url = '/portal/volunteer/reports?nid=' + nid;
    $('.reports a').attr('href', report_url);
    //活动招募更多url
    var recruite_url = '/portal/volunteer/recruite?nid=' + nid;
    $('.recruite a').attr('href', recruite_url);

    /*获取banner*/
    var token = getCookie('token');
    $.ajax({
        url: '/api/volunteer/getBanner',
        type: 'GET',
        dataType: 'json',
        data: {},
        success: function(res) {
            if (res.status == 1) {
                var tpl = $("#banner-template").html();
                //预编译模板
                var template = Handlebars.compile(tpl);
                var html = template(res.data);
                //输入模板
                $('#carousel').html(html);
                // var swiper = new Swiper('.swiper-container', {
                //     autoplay: {
                //         delay: 2500,
                //         disableOnInteraction: false,
                //     },
                //     loop: true,
                //     pagination: {
                //         el: '.swiper-pagination',
                //         clickable: true,
                //     },
                // });
                layui.use('carousel', function() {
                    var carousel = layui.carousel;
                    //建造实例
                    carousel.render({
                        elem: '#banner',
                        width: '100%' //设置容器宽度
                            ,
                        height: '444px',
                        autoplay: true,
                        interval: 2500,
                        arrow: 'none' //始终显示箭头
                        //,anim: 'updown' //切换动画方式
                    });
                });
            }
        }
    })
    /*活动报道*/
    $.ajax({
        url: '/api/volunteer/getReport',
        type: 'GET',
        dataType: 'json',
        data: {},
        success: function(res) {
            if (res.status == 1) {
                var tpl = $("#reports-template").html();
                //预编译模板
                var template = Handlebars.compile(tpl);
                var time = res.data;
                for (var i in time) {
                    time[i].published_time = data_format(time[i].published_time);
                }
                var html = template(time.slice(0, 3));

                //输入模板
                $('#reports').html(html);
            }
        }
    })
    /*活动招募*/
    $.ajax({
        url: '/api/volunteer/getrecurit',
        type: 'GET',
        dataType: 'json',
        data: {},
        success: function(res) {
            if (res.status == 1) {
                var tpl = $("#recurit-template").html();
                //预编译模板
                var template = Handlebars.compile(tpl);
                var time = res.data;
                var nowdata = new Date().getTime() / 1000;
                for (var i in time) {
                    time[i].start_time = data_format(time[i].start_time);
                    time[i].end_time = data_format(time[i].end_time);

                }
                var html = template(time.slice(0, 2));
                //输入模板
                $('#recurit').html(html);
                // for (var i in time) {
                //     if (nowdata > time[i].baoming_end_time) {
                //         $('.button').eq(i).html('已过期');
                //         $('.button').eq(i).removeClass('activity-active');
                //         $('.button').eq(i).addClass('expire');
                //     }
                //     if (nowdata > time[i].end_time) {
                //         $('.button').eq(i).html('已过期');
                //         $('.button').eq(i).removeClass('activity-active');
                //         $('.button').eq(i).addClass('expire');
                //     }
                // }
            }
        }
    })
    /*风采展示*/
    $.ajax({
        url: '/api/volunteer/getMien',
        type: 'GET',
        dataType: 'json',
        data: {},
        success: function(res) {
            if (res.status == 1) {
                var tpl = $("#elegant-template").html();
                //预编译模板
                for (var i in res.data) {
                    if (res.data[i].imgs != null) {
                        res.data[i].imgs = res.data[i].imgs[0];
                    }
                }
                var template = Handlebars.compile(tpl);
                var html = template(res.data);

                //输入模板
                $('#elegant').html(html);
                var html = '';

                if (res.data.length < 6) {
                    var swiper1 = new Swiper('.swiper-container1', {

                        navigation: {
                            nextEl: '.swiper-button-next',
                            prevEl: '.swiper-button-prev',
                        },
                    });
                    $('.swiper-container1 .swiper-slide').css('width', '676px');
                    $('.swiper-container1 .swiper-slide').css('height', '256px');
                    $('.swiper-container1 .swiper-slide img').css('width', '676px');
                    $('.swiper-container1 .swiper-slide img').css('height', '256px');
                    $('.swiper-slide h3').css('width', '638px');
                } else if (res.data.length > 6) {
                    var swiper1 = new Swiper('.swiper-container1', {
                        slidesPerView: 3,
                        slidesPerColumn: 2,
                        spaceBetween: 8,
                        navigation: {
                            nextEl: '.swiper-button-next',
                            prevEl: '.swiper-button-prev',
                        },
                    });
                } else if (res.data.length == 6) {
                    var swiper1 = new Swiper('.swiper-container1', {
                        slidesPerView: 3,
                        slidesPerColumn: 2,
                        spaceBetween: 8,
                        navigation: {
                            nextEl: '.swiper-button-next',
                            prevEl: '.swiper-button-prev',
                        },
                    });
                    $('.swiper-button-next').hide();
                    $('.swiper-button-prev').hide();
                } else if (res.data, length == 0) {
                    $('.swiper-button-next').hide();
                    $('.swiper-button-prev').hide();
                }
            }
        }
    })
    /*志愿者活跃度*/
    $.ajax({
        url: '/api/volunteer/VolunScore',
        type: 'GET',
        dataType: 'json',
        data: {},
        success: function(res) {
            if (res.status == 1) {
                var tpl = $("#active-template").html();
                //预编译模板
                var template = Handlebars.compile(tpl);
                var html = template(res.data.slice(0, 11));

                //输入模板
                $('#active').html(html);
                var num = res.data;
                for (var i in num) {
                    console.log(i)
                    if (num[i].avatar == '') {
                        $('.volunteers li img').eq(i).attr('src', '/themes/simpleboot3/public/assets/whgcms/images/my/avatar.png');
                    }
                    $('.paiming').eq(i).html(Number(i) + 1 + '.');

                }
            }
        }
    })

    /*志愿者总数*/
    $.ajax({
        url: '/api/volunteer/volun_count',
        type: 'GET',
        dataType: 'json',
        data: {},
        success: function(res) {
            $('.volunteer .count').html(res.data.num);
        }
    })
    $('#reg').click(function(event) {
        if (token == '') {
            layui.use('layer', function() {
                layer.open({
                    title: '提示',
                    content: '请先登录',
                    btn: ['确认', '取消'],
                    yes: function(index, layero) {
                        window.location.href = '/portal/login/login';
                    },
                    btn2: function(index, layero) {
                        layer.closeAll();
                    },
                    cancel: function() {
                        return false;
                    },
                    btnAlign: 'c',
                    shade: 0.3,
                    scrollbar: false
                });
            });
        } else {
            request({
                url: '/api/volunteer/auth_read',
                type: 'GET',
                dataType: 'json',
                data: {},
                success: function(res) {
                    if (res.status == 1) {
                        window.location.href = "/portal/volunteer/sign";
                    } else {
                        if (res.code == '13011') {
                            getdialog('您已成为志愿者');
                        } else if (res.code == '13012') {
                            getdialog('您已注册，等待审核');
                        }
                    }

                }
            }, true)
        }

    });


})
$(function() {
    //banner模板
    /********************main部分*******************/
    /*模板6*/
    $('body').on('click', '.mainModule-6-content li', function() {
        var index = $(this).index();
        var img = $(this).css('background-image');
        var title1 = $(this).children('.title1').html();
        var title2 = $(this).children('.title2').html();
        var url = $(this).data('url');
        var zhidingsrc = $(this).children('.zhiding1').attr('src');
        $(this).parent().parent().find('.amplification').css('background-image', img);
        $(this).parent().parent().find('.introduce1').html(title1);
        $(this).parent().parent().find('.introduce2').html(title2);
        $(this).parent().parent().find('.inline').attr('href', url);
        if (zhidingsrc == undefined) {
            $(this).parent().parent().find('.zhidingactive').hide();
        } else {
            $(this).parent().parent().find('.zhidingactive').show();
        }
        // $(this).parent().parent().find('.zhiding1').attr('src',zhidingsrc);
    })

    function nav(navwidth) {
        $.ajax({
            url: '/api/category/menu',
            dataType: 'json',
            async: false,
            success: function(res) {
                var liwidth = 0;
                var mr = 0;
                var flag = true;
                if (res.status == 1) {
                    if (window.location.href == 'http://' + document.domain + '/portal/my/index') {
                        var html = '<li><a id="nav0" href="/">首页</a></li>';

                    } else {
                        var html = '<li><a id="nav0" class="active" href="/">首页</a></li>';
                    }
                    $('.nav').append(html);
                    liwidth += $('.nav>li').width();
                    for (var i in res.data) {
                        var item = res.data[i];
                        if (liwidth > navwidth) {
                            //更多
                            if (flag == true) {
                                //去除当前最后一个nav item 添加更多item后将其加入更多的子列表中
                                var index = $('.nav li:last-child').index();
                                html = '<li class="more" style="position: relative;cursor: pointer;"><a href="javascript:;">更多</a><i class="layui-icon" style="font-size: 14px;margin-left: 3px;color:#fff;">&#xe61a;</i><ul class="navmore"></ul></li>';
                                var lastchild = '<li>' + $('.nav li:last-child').html() + '</li>';
                                $('.nav li:last-child').remove();
                                $('.nav').append(html);
                                lastchild += '<li><a id="nav' + item['id'] + '" href="' + item['url'] + '" target="' + item['target'] + '">' + item['title'] + '</a></li>';
                                $('.nav .navmore').append(lastchild);
                                flag = false;
                            } else {
                                //往更多里写
                                html = '<li><a id   ="nav' + item['id'] + '" href="' + item['url'] + '" target="' + item['target'] + '">' + item['title'] + '</a></li>';
                                $('.nav .navmore').append(html)
                            }
                        } else {
                            html = '<li><a id="nav' + item['id'] + '" href="' + item['url'] + '" target="' + item['target'] + '">' + item['title'] + '</a></li>';
                            $('.nav').append(html);
                        }
                        liwidth += $('.nav>li:last-child').width() + 32;
                    }
                }
            }
        })
    }
    //header
    function headerModule(data, templateid) {
        if (templateid == 1) {
            // header模板1
            var headerModule1Tpl = $('#headerModule1-temeplate').html();
            var headerModule1Cmp = Handlebars.compile(headerModule1Tpl);
            $('#header').html(headerModule1Cmp(data))
            nav(1150);
        } else if (templateid == 2) {
            // header模板2
            var headerModule2Tpl = $('#headerModule2-temeplate').html();
            var headerModule2Cmp = Handlebars.compile(headerModule2Tpl);
            $('#header').html(headerModule2Cmp(data))
            nav(650)
        } else if (templateid == 3) {
            // header模板3
            var headerModule3Tpl = $('#headerModule3-temeplate').html();
            var headerModule3Cmp = Handlebars.compile(headerModule3Tpl);
            $('#header').html(headerModule3Cmp(data))
            //headerM3对应banner模板
            $.ajax({
                type: 'get',
                url: '/api/category/getdata',
                dataType: 'json',
                async: false,
                data: {
                    id: 6
                },
                success: function(bannerdata) {
                    if (bannerdata.status == 1) {
                        var bannerhtml = '';
                        for (var i = 0; i < bannerdata.data.length; i++) {
                            bannerhtml += '<div class="swiper-slide">';
                            bannerhtml += '<a href=' + bannerdata.data[i].url + '><img src=' + bannerdata.data[i].thumb + '>';
                            bannerhtml += '</a></div>';
                        }
                        $('.headerModule-3 .swiper-wrapper').html(bannerhtml);


                    }

                }
            })
            var header3swiper = new Swiper('.headerModule-3 .swiper-container', {
                autoplay: 2000, //可选选项，自动滑动
                loop: true, //可选选项，开启循环
                speed: 500,
                pagination: '.swiper-pagination3',
                paginationClickable: true
            })
            // var header3swiper = new Swiper('.headerModule-3 .swiper-container', {
            //     autoplay: 2000,
            //     speed: 500,
            //     autoplayDisableOnInteraction: false,
            //     // // direction: 'vertical',
            //     loop: true,
            //     effect: 'fade',
            //     fade: {
            //         crossFade: false,
            //     },
            //     // slidesPerView: 1,
            //     // 如果需要分页器
            //     pagination: '.swiper-pagination3',
            //     paginationType: 'bullets',
            //     paginationClickable: true
            // });
            nav(1150)
        }
        checkLogin();
    }
    //banner
    function bannerModule(data, templateid) {
        if (templateid == 4) {
            // banner模板1
            var bannerModule1Tpl = $('#bannerModule1-temeplate').html();
            var bannerModule1Cmp = Handlebars.compile(bannerModule1Tpl);
            $('#banner').html(bannerModule1Cmp(data));
            //banner模板1
            var swiper = new Swiper('.bannerModule-1 .swiper-container', {
                autoplay: 2000,
                speed: 500,
                autoplayDisableOnInteraction: false,
                // // direction: 'vertical',
                loop: true,
                // slidesPerView: 1,
                // 如果需要分页器
                pagination: '.swiper-pagination',
                paginationType: 'bullets',
            });
        } else if (templateid == 5) {
            // banner模板2
            var bannerModule2Tpl = $('#bannerModule2-temeplate').html();
            var bannerModule2Cmp = Handlebars.compile(bannerModule2Tpl);
            $('#banner').html(bannerModule2Cmp(data));
            //banner模板2
            var swiper2 = new Swiper('.bannerModule-2 .swiper-container', {
                autoplay: 2000,
                speed: 500,
                autoplayDisableOnInteraction: false,
                // // direction: 'vertical',
                loop: true,
                // slidesPerView: 1,
                paginationType: 'bullets',
                //加左右进退按钮
                prevButton: '.swiper-button-prev',
                nextButton: '.swiper-button-next',
            });
            //鼠标移入出面左右箭头，移除隐藏
            $(".bannerModule-2-content").hover(function() {
                $(".bannerModule-2 .swiper-button-prev").show();
                $(".bannerModule-2 .swiper-button-next").show();
            }, function() {
                $(".bannerModule-2 .swiper-button-prev").hide();
                $(".bannerModule-2 .swiper-button-next").hide();
            });
        } else if (templateid == 6) {
            console.log('banner')
            // banner模板3
            var bannerModule3Tpl = $('#bannerModule3-temeplate').html();
            var bannerModule3Cmp = Handlebars.compile(bannerModule3Tpl);
            $('#banner').html(bannerModule3Cmp(data));
            //banner模板3
            var swiper3 = new Swiper('.bannerModule-3 .swiper-container', {
                autoplay: 2000,
                speed: 500,
                autoplayDisableOnInteraction: false,
                loop: true,
                centeredSlides: true,
                slidesPerView: 2,
                pagination: '.swiper-pagination',
                paginationClickable: true,
                prevButton: '.swiper-button-prev',
                nextButton: '.swiper-button-next',
                onInit: function(swiper) {
                    swiper.slides[2].className = "swiper-slide swiper-slide-active"; //第一次打开不要动画
                },
                breakpoints: {
                    668: {
                        slidesPerView: 2,
                    }
                }
            });
        }
    }
    //main
    var m6leftdata = [];
    var m7leftdata = [];

    function mainModule(data, templateid) {
        if (templateid == 7) {
            // main模板1
            data.listdata = data.listdata.slice(0, 3);
            var mainModule1Tpl = $('#mainModule1-temeplate').html();
            var mainModule1Cmp = Handlebars.compile(mainModule1Tpl);
            $('#main').append(mainModule1Cmp(data));
        } else if (templateid == 8) {
            // main模板2
            data.listdata = data.listdata.slice(0, 8);
            var mainModule2Tpl = $('#mainModule2-temeplate').html();
            var mainModule2Cmp = Handlebars.compile(mainModule2Tpl);
            $('#main').append(mainModule2Cmp(data));
        } else if (templateid == 9) {
            data.listdata = data.listdata.slice(0, 4);
            // main模板3
            var mainModule3Tpl = $('#mainModule3-temeplate').html();
            var mainModule3Cmp = Handlebars.compile(mainModule3Tpl);
            $('#main').append(mainModule3Cmp(data));
        } else if (templateid == 10) {
            // main模板4
            data.listdata = data.listdata.slice(0, 3);
            var mainModule4Tpl = $('#mainModule4-temeplate').html();
            var mainModule4Cmp = Handlebars.compile(mainModule4Tpl);
            $('#main').append(mainModule4Cmp(data));
            /*模板4*/
            $('#featured-area ul').roundabout({
                easing: 'easeOutInCirc',
                duration: 600
            });
        } else if (templateid == 11) {
            // main模板5
            data.listdata = data.listdata.slice(0, 3);
            var mainModule5Tpl = $('#mainModule5-temeplate').html();
            var mainModule5Cmp = Handlebars.compile(mainModule5Tpl);
            $('#main').append(mainModule5Cmp(data));
        } else if (templateid == 12) {
            // main模板6
            data.listdata = data.listdata.slice(0, 5);
            var mainModule6Tpl = $('#mainModule6-temeplate').html();
            var mainModule6Cmp = Handlebars.compile(mainModule6Tpl);
            $('#main').append(mainModule6Cmp(data));
            //左侧数据
            m6leftdata.push(data.listdata[0]);

        } else if (templateid == 13) {
            // for(var i= 0;i<data.length;i++){
            //     if(i==0){
            //         console.log(111)
            //         var dataleft = data.listdata[0]
            //         $('.list-left .thumb').attr('src',dataleft.thumb);
            //         $('.listLeft-jianjie h3').text(dataleft.title);
            //         $('.listLeft-jianjie .jianjie').text(dataleft.abstract);
            //         $('.listLeft-jianjie .time').text(dataleft.time);
            //         $('.list-left>a').attr('href','/portal/category/read/?id='+dataleft.id+'&cid=19')
            //         // if(dataleft.is_top == 1){
            //         //     $('.zhiding1').show();
            //         // }else{
            //         //     $('.zhiding1').hide();
            //         // }
            //     }
            // }
            //左侧数据
            m7leftdata.push(data.listdata[0]);
            data.listdata = data.listdata.slice(1, 4);
            // var tpl = $('#article-list').html();
            // var template = Handlebars.compile(tpl);
            // $('#list-right').append(template(dataright));

            // for(var i = 0; i<dataright.length; i++){
            //     if(data.listdata.is_top == 1){
            //         $('.zhiding2').eq(i).show();
            //     }else{
            //         $('.zhiding2').eq(i).hide();
            //     }
            // }
            // main模板7
            var mainModule7Tpl = $('#mainModule7-temeplate').html();
            var mainModule7Cmp = Handlebars.compile(mainModule7Tpl);
            $('#main').append(mainModule7Cmp(data));
        }
    }

    $.ajax({
        type: 'get',
        url: '/api/category',
        dataType: 'json',
        success: function(res) {
            // console.log(res)
            if (res.status == 1) {
                var data = res.data;
                for (var i = 0; i < data.length; i++) {
                    if (i == 0) {
                        // headerModule(data[i], data[i].template_id);
                        $('.load').hide();
                        $('.footer').show();
                    } else if (i == 1 && data[i].id == 6) {
                        var dat = data[i]
                        $.ajax({
                            type: 'get',
                            url: '/api/category/getdata',
                            dataType: 'json',
                            async: false,
                            data: {
                                id: data[i].id
                            },
                            success: function(listres) {
                                // console.log(listres);
                                if (listres.status == 1) {
                                    listdata = listres.data
                                    dat.listdata = listdata;
                                }
                            }
                        })
                        bannerModule(dat, dat.template_id);

                    } else if (data[1].id != 6 && i >= 1 && i < data.length - 1) {
                        // console.log(i);
                        var dat = data[i];
                        $.ajax({
                            type: 'get',
                            url: '/api/category/getdata',
                            dataType: 'json',
                            async: false,
                            data: {
                                id: data[i].id
                            },
                            success: function(listres) {
                                // console.log(listres);
                                if (listres.status == 1) {
                                    listdata = listres.data
                                    dat.listdata = listdata;
                                    for (var i = 0; i < dat.listdata.length; i++) {
                                        dat.listdata[i].time = data_format(dat.listdata[i].time)
                                    }
                                }
                            }
                        })
                        // console.log(dat)
                        mainModule(dat, dat.template_id);
                    } else if (data[1].id == 6 && i >= 2 && i < data.length - 1) {
                        var dat = data[i];
                        $.ajax({
                            type: 'get',
                            url: '/api/category/getdata',
                            dataType: 'json',
                            async: false,
                            data: {
                                id: data[i].id
                            },
                            success: function(listres) {
                                // console.log(listres);
                                if (listres.status == 1) {
                                    listdata = listres.data
                                    dat.listdata = listdata;
                                    for (var i = 0; i < dat.listdata.length; i++) {
                                        dat.listdata[i].time = data_format(dat.listdata[i].time)
                                    }
                                }
                            }
                        })
                        // console.log(dat)
                        mainModule(dat, dat.template_id);
                    } else if (i == data.length - 1) {
                        var aboutusTpl = $('#aboutus-temeplate').html();
                        var aboutusCmp = Handlebars.compile(aboutusTpl);
                        // console.log(data);
                        $('#aboutus').append(aboutusCmp(data[data.length - 1]));
                        // 关于我们模板
                        $.ajax({
                            type: 'get',
                            url: '/api/category/getdata',
                            dataType: 'json',
                            async: false,
                            data: {
                                id: 20
                            },
                            success: function(aboutdata) {
                                // console.log(aboutdata);
                                if (aboutdata.status == 1) {
                                    $('.about').html(aboutdata.data);
                                }
                            }
                        })

                    }
                }
                //修改main模板6左侧数据
                for (var i = 0; i < m6leftdata.length; i++) {
                    var m6leftdom = $('.amplification');
                    for (var j = 0; j < m6leftdom.length; j++) {
                        // console.log(m6leftdata)
                        if (i == j) {
                            m6leftdom.eq(i).css('background-image', 'url(' + m6leftdata[i].thumb + ')');
                            $('.introduce1').eq(i).text(m6leftdata[i].title);
                            $('.introduce2').eq(i).text(m6leftdata[i].abstract);
                            $('.mainModule-6 .content  a').attr('href', m6leftdata[i].url);
                            if (m6leftdata[i].is_top == 1) {
                                $('.amplification .zhiding1').eq(i).show();
                            } else {
                                $('.amplification .zhiding1').eq(i).hide();
                            }
                        }
                    }
                }
                //修改main模板7左侧数据
                for (var i = 0; i < m7leftdata.length; i++) {
                    var m7leftdom = $('.list-left');
                    for (var j = 0; j < m7leftdom.length; j++) {
                        if (i == j) {
                            if (m7leftdata[i] == undefined) {
                                $('.mainModule-7').eq(i).find('.list-left').hide();
                            } else {
                                $('.list-left .thumb').eq(i).attr('src', m7leftdata[i].thumb);
                                $('.listLeft-jianjie h3').eq(i).text(m7leftdata[i].title);
                                $('.listLeft-jianjie .jianjie').eq(i).text(m7leftdata[i].abstract);
                                $('.listLeft-jianjie .time').eq(i).text(m7leftdata[i].time);
                                $('.list-left>a').eq(i).attr('href', m7leftdata[i].url)
                                if (m7leftdata[i].is_top == 1) {
                                    $('.list-left .zhiding1').eq(i).show();
                                } else {
                                    $('.list-left .zhiding1').eq(i).hide();
                                }
                            }
                        }
                    }
                }
            }
        }
    })
    // $('.footer').css('display','block');

});
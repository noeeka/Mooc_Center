$(function() {
    // require(['/themes/simpleboot3/public/assets/whgcms/js/header.js']);
    // require(['/themes/simpleboot3/public/assets/whgcms/js/footer.js']);
    Handlebars.registerHelper('compare', function(opera, a, b, options) {
        switch (opera) {
            case 'gt':
                return a > b ? options.fn(this) : options.inverse(this);
            case 'lt':
                return a < b ? options.fn(this) : options.inverse(this);
            case 'egt':
                return a >= b ? options.fn(this) : options.inverse(this);
            case 'elt':
                return a <= b ? options.fn(this) : options.inverse(this);
        }
    });
    Handlebars.registerHelper('gtlt', function(a, b, c,options) {

        return a>b && a<c ? options.fn(this) : options.inverse(this);

    });
    //活动报名状态
    Handlebars.registerHelper("baoming", function(attr, options) {
        var timestamp = Date.parse(new Date()) / 1000;
        // console.log(timestamp);
        //直接参与
        if(attr['need_baoming'] == 0 && attr['start_time'] <= timestamp && timestamp <= attr['end_time'] ) {
            return "<img src=\"/themes/simpleboot3/public/assets/whgcms/images/baoming-start.png\" alt=\"\" class=\"baoming\">";
        }
        //直接参与未开始
        if(attr['need_baoming'] == 0 && attr['start_time'] >= timestamp &&  attr['end_time'] >= timestamp  ) {
            return"<img src=\"/themes/simpleboot3/public/assets/whgcms/images/baoming-nostart.png\" alt=\"\" class=\"baoming\">";
        }
        //未开始
        if(attr['need_baoming'] == 1&&attr['baoming_start_time'] > timestamp &&  attr['baoming_end_time'] > timestamp  ) {
            return "<img src=\"/themes/simpleboot3/public/assets/whgcms/images/baoming-nostart.png\" alt=\"\" class=\"baoming\">";
        }
        //报名
        if(attr['need_baoming'] == 1&&timestamp >= attr['baoming_start_time'] && timestamp <= attr['baoming_end_time']){
            return "<img src=\"/themes/simpleboot3/public/assets/whgcms/images/baoming-start.png\" alt=\"\" class=\"baoming\">";
        }
        //已过期
        return "<img src=\"/themes/simpleboot3/public/assets/whgcms/images/baoming-end.png\" alt=\"\" class=\"baoming\">";

    });
    var globalCss = [];
    var tplObj = {};
    $.ajax({
        url: '/api/homepage/pc',
        type: 'get',
        dataType: 'json',
        async: false,
        success: function(res) {
            console.log(res)
            if (res.status == 1) {
                var maindata = res.data;
                //list区域模板
                var mainAreaTpl = $('#mainArea-tpl').html();
                var mainAreaCmp = Handlebars.compile(mainAreaTpl);
                // console.log(maindata)
                for (var i = 0; i < maindata.length; i++) {
                    //添加区域模板
                    $('#main').append(mainAreaCmp(maindata[i]));
                    var parent_id = maindata[i]['id'];
                    //list模板
                    var sub = maindata[i].sub;
                    for (var j = 0; j < sub.length; j++) {
                        $.ajax({
                            url: sub[j].api_url,
                            type: 'get',
                            dataType: 'json',
                            async: false,
                            data: {
                                start: sub[j].start,
                                len: sub[j].len
                            },
                            success: function(dat) {
                                if(dat.status == 1){
                                    //添加List模板
                                    var tpl_id = sub[j]['tpl_id'];
                                    if (globalCss.indexOf(tpl_id) == -1) {
                                        globalCss[globalCss.length] = sub[j]['tpl_id'];
                                        $('#global-css').append(sub[j]['tpl_css']);
                                        tplObj[tpl_id] = Handlebars.compile(sub[j]['tpl_code']);
                                    }
                                    var subCmp = tplObj[tpl_id];
                                    //格式化时间戳
                                    for (var z = 0; z < dat.data.length; z++) {
                                        //月日
                                        dat.data[z].monthDay = data_format(dat.data[z].time, '-', true, true);
                                        //年月日
                                        dat.data[z].time = data_format(dat.data[z].time, '-', true);
                                    }
                                    sub[j]['data'] = dat.data;
                                    $('#area' + parent_id).find('.sublist').append(subCmp(sub[j]));
                                }

                            }
                        })
                    }
                }
            }

        }
    })
    //模板特效
    /*模板4*/
    $('#featured-area ul').roundabout({
        easing: 'easeOutInCirc',
        duration: 600
    });
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
    })
    /*模板8*/
    for (var i = 0; i < $('.mainModule-8').length; i++) {
        // var container8 = $('.mainModule-30-content .swiper-container')[i];
        // var pagination8 = $('.mainModule-30-content .pagination')[i];
        // var container8 = $('.mainModule-30').eq(i).child('.swiper-container');
        // console.log(container8)
        // var pagination8 = $('.mainModule-30').eq(i).child('.pagination');
        var container8 =  $('.mainModule-8').eq(i).attr("id");
        console.log('#'+container8 +' .swiper-container')
        new Swiper('#'+container8 +' .swiper-container', {
            autoplay: 2500, //可选选项，自动滑动
            loop: true, //可选选项，开启循环
            pagination: '#' +container8 + ' .pagination',
        })
    }
    /*模板17*/
    $('.mainModule-17-content .list .tupian').hover(function() {
        $(this).siblings().css('background', '#5269B9');
        $(this).children().eq(1).hide();
        $(this).children().eq(2).show();
        $(this).siblings().children().eq(0).hide();
        $(this).siblings().children().eq(1).show();
    }, function() {
        $(this).siblings().css('background', '#fff');
        $(this).children().eq(1).show();
        $(this).children().eq(2).hide();
        $(this).siblings().children().eq(0).show();
        $(this).siblings().children().eq(1).hide();
    })

    /*模板10*/
    $('.mainModule-10-list li').mouseover(function(event) {
        $(this).find('h3').css('top', '0');
        $(this).find('h3').addClass('active');
        $(this).find('em').addClass('active1');
        $('.mainModule-10-list li').eq(0).find('em').css('max-height', '72%');
    });
    $('.mainModule-10-list li').mouseout(function(event) {
        $(this).find('h3').css('top', '78%');
        $('.mainModule-10-list li').eq(0).find('h3').css('top', '89%');
        $(this).find('h3').removeClass('active');
        $(this).find('em').removeClass('active1');
    });
    /*模板15*/
    $('.mainModule-15-list>div').mouseover(function(event) {
        $(this).find('h3').css('top', '0');
        $(this).find('h3').addClass('active');
        $(this).find('em').addClass('active1');
    });
    $('.mainModule-15-list>div').mouseout(function(event) {
        $(this).find('h3').css('top', '162px');
        $(this).find('h3').removeClass('active');
        $(this).find('em').removeClass('active1');
    });
    // 模板20
    for (var i = 0; i < $('.mainModule-20').length; i++) {
         var container20 =  $('.mainModule-20').eq(i).attr("id");
         new Swiper('#'+container20 +' .swiper-container', {
            autoplay: 2500, //可选选项，自动滑动
            loop: true, //可选选项，开启循环
            pagination: '#' +container20 + ' .pagination',
        })
    }
    // 模板50
    for (var i = 0; i < $('.mainModule-50').length; i++) {
        var container50 =  $('.mainModule-50').eq(i).attr("id");
        var mySwiper50 = new Swiper('#'+container50 +' .swiper-container', {
            autoplay: 2500, //可选选项，自动滑动
            loop: true //可选选项，开启循环
        })
        // console.log($('#'+container50 +' .arrow-left'))
        $('#'+container50 +' .arrow-left').on('click', function(e){
            e.preventDefault()
            mySwiper50.swipePrev()
        })
        $('#'+container50 +' .arrow-right').on('click', function(e){
            e.preventDefault()
            mySwiper50.swipeNext()
        })
        //鼠标移入出面左右箭头，移除隐藏
        $('#'+container50 +' .swiper-container').hover(function() {
            $('#'+container50 +' .arrow-left').show();
            $('#'+container50 +' .arrow-right').show();
        }, function() {
            $('#'+container50 +' .arrow-left').hide();
            $('#'+container50 +' .arrow-right').hide();
        });
    }
    // 模板52
    for (var i = 0; i < $('.mainModule-52').length; i++) {
        var container52 =  $('.mainModule-52').eq(i).attr("id");
        var mySwiper52 = new Swiper('#'+container52 +' .swiper-container', {
            autoplay: 2500, //可选选项，自动滑动
            loop: true, //可选选项，开启循环
            grabCursor: true,
            paginationClickable: true,
            pagination: '#' +container52 + ' .pagination',
        })

    }
})
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
                                $('#area' + parent_id).find('.sublist').append(subCmp(sub[j]))
                            }
                        })
                    }
                }
            }

        }
    })
    //模板特效
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
    /*模板8*/
    for (var i = 0; i < $('.mainModule-8').length; i++) {
        var container = $('.mainModule-8-content .swiper-container')[i];
        var pagination = $('.mainModule-8-content .pagination')[i];
        var mySwiper = new Swiper(container, {
            autoplay: 2500, //可选选项，自动滑动
            loop: true, //可选选项，开启循环
            pagination: pagination,
        })
    }
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
})
$(function(){
    //导航
    // var swiper2 = new Swiper('.navHead .swiper-container', {
    //     // autoplay: 2000,
    //     // speed: 500,
    //     autoplayDisableOnInteraction: false,
    //
    //     grabCursor : true,
    //     slidesPerView: 4,
    //     // loop: true,
    //     loopFillGroupWithBlank: true,
    //     pagination: '.navHead .swiper-pagination'//分页容器的css选择器
    //
    // });

    //修改banner样式
    function bannerSwiper(){
        var swiper = new Swiper('.banner .swiper-container', {
            autoplay: 2000,
            speed: 500,
            autoplayDisableOnInteraction: false,
            loop: true,
            grabCursor : true,
            parallax:true,
            slidesPerView: 1,
            pagination: {
                el: '.banner .swiper-pagination',
            }//分页容器的css选择器

        });

    }
    //导航1模板
    var nav1Tpl = $('#nav1-tpl').html();
    var nav1Cmp = Handlebars.compile(nav1Tpl);
    //导航2模板
    var nav2Tpl = $('#nav2-tpl').html();
    var nav2Cmp = Handlebars.compile(nav2Tpl);
    //banner数据模板
    var bannerTpl = $('#banner-template').html();
    var bannerCmp = Handlebars.compile(bannerTpl);
    //最新动态数据模板
    var newsTpl = $('#news-template').html();
    var newsCmp = Handlebars.compile(newsTpl);
    //资源展示数据模板
    var sourcesTpl = $('#sources-template').html();
    var sourcesCmp = Handlebars.compile(sourcesTpl);
    //活动信息数据模板
    var activityTpl = $('#activity-template').html();
    var activityCmp = Handlebars.compile(activityTpl);
    //文化点单数据模板
    var cultureTpl = $('#culture-template').html();
    var cultureCmp = Handlebars.compile(cultureTpl);
    //导航
    $.ajax({
        type:'get',
        url:'/api/navigation/index/type/2',
        success:function(res){
            // console.log(res);
            var data = res.data;
            for(var i = 0;i<data.length;i++){
                if(data[i].is_show==1){
                    $('#nav2').append(nav2Cmp(data[i]))
                }else{
                    $('#swiper-wrapper1').append(nav1Cmp(data[i]));
                }
            }
            var swiper2 = new Swiper('.navHead .swiper-container', {
                slidesPerView: 4,
                // spaceBetween: 30,
                slidesPerGroup: 4,
                loop: true,
                loopFillGroupWithBlank: true,
                pagination: {
                    el: '.navHead .swiper-pagination',
                }
            });
        }
    })
    // $.ajax({
    //     type:'get',
    //     url:'/api/article/index?cid=12&len=2',
    //     async: false,
    //     success:function(res){
    //         $('.sourcescontent').html(sourcesCmp(res.data.list))
    //     }
    // })
    //活动报名状态
    Handlebars.registerHelper("baoming", function(attr, options) {
        var timestamp = Date.parse(new Date()) / 1000;
        //直接参与
        if(attr['need_baoming'] == 0 && attr['start_time'] <= timestamp && timestamp <= attr['end_time'] ) {
            return "<img src=\"/themes/simpleboot3/public/assets/whgcms/wx/images/baoming-start.png\" class=\"baomingState\"  alt=\"\">";
        }
        //直接参与未开始
        if(attr['need_baoming'] == 0 && attr['start_time'] >= timestamp &&  attr['end_time'] >= timestamp  ) {
            return"<img src=\"/themes/simpleboot3/public/assets/whgcms/wx/images/baoming-nostart.png\" class=\"baomingState\"  alt=\"\">";
        }
        //未开始
        if(attr['need_baoming'] == 1&&attr['baoming_start_time'] > timestamp &&  attr['baoming_end_time'] > timestamp  ) {
            return "<img src=\"/themes/simpleboot3/public/assets/whgcms/wx/images/baoming-nostart.png\" class=\"baomingState\"  alt=\"\">";
        }
        //报名
        if(attr['need_baoming'] == 1&&timestamp >= attr['baoming_start_time'] && timestamp <= attr['baoming_end_time']){
            return "<img src=\"/themes/simpleboot3/public/assets/whgcms/wx/images/baoming-start.png\" class=\"baomingState\"  alt=\"\">";
        }
        //已过期
        return "<img src=\"/themes/simpleboot3/public/assets/whgcms/wx/images/baoming-end.png\" class=\"baomingState\"  alt=\"\">";

    });
    Handlebars.registerHelper("time", function(time, options) {
        var date = new Date(time * 1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
        Y = date.getFullYear() + '-';
        M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-';
        D = date.getDate() + ' ';
        h = date.getHours() + ':';
        m = date.getMinutes()>9?date.getMinutes():'0'+date.getMinutes();
        s = date.getSeconds();
        return Y+M+D+h+m;
    })
    var globalCss = [];
    var tplObj = {};
    $.ajax({
        url: '/api/homepage/pc?type=1',
        type: 'get',
        dataType: 'json',
        async: false,
        success: function(res) {
            console.log(res)
            if (res.status == 1) {
                var maindata = res.data;
                // console.log(maindata)
                // for (var i = 0; i < maindata.length; i++) {
                //     var parent_id = maindata[i]['id'];
                    //list模板
                    // var sub = maindata[i].sub;
                    for (var j = 0; j < maindata.length; j++) {
                        $.ajax({
                            url: maindata[j].api_url,
                            type: 'get',
                            dataType: 'json',
                            async: false,
                            data: {
                                start: maindata[j].start,
                                len: maindata[j].len
                            },
                            success: function(dat) {
                                if(dat.status == 1){
                                    //添加List模板
                                    var tpl_id = maindata[j]['tpl_id'];
                                    if (globalCss.indexOf(tpl_id) == -1) {
                                        globalCss[globalCss.length] = maindata[j]['tpl_id'];
                                        $('#global-css').append(maindata[j]['tpl_css']);
                                        tplObj[tpl_id] = Handlebars.compile(maindata[j]['tpl_code']);
                                    }
                                    var subCmp = tplObj[tpl_id];
                                    maindata[j]['data'] = dat.data;
                                    // $('#area' + parent_id).find('.sublist').append(subCmp(sub[j]));
                                    $('#main').append(subCmp(maindata[j]))
                                }

                            }
                        })
                    }
                // }
            }

        }
    })
    $.ajax({
        type: 'get',
        url: '/api/index/getIndexData',
        dataType: 'json',
        async: false,
        success: function(res) {
            if(res.status == 1){
                // console.log(res);
                //banner
                var bannerData = res.data.banner;
                for(var i = 0; i< bannerData.length;i++){
                    if(bannerData[i].murl == ''){
                        bannerData[i].murl = 'javascript:;';
                    }else{
                        bannerData[i].murl = '/wx/Readiframe/index?srcid='+bannerData[i].murl+'&title=江西省群众艺术馆';
                    }
                }
                $('#swiper-wrapper').html(bannerCmp(bannerData));
                $('#swiper-wrapper .swiper-slide:first-child').addClass('swiper-slide-center')
                    .addClass('none-effect');
                activeIndex = bannerData.length +1
                bannerSwiper();
            }

        }
    });

})
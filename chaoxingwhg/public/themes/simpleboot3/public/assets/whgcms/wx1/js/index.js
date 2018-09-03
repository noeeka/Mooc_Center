$(function(){
    //修改banner样式
    // var activeIndex = 0;
    function bannerSwiper(){
       /* var swiper_container = document.getElementsByClassName('swiper-container')
        var swiper_slide = document.getElementsByClassName('swiper-slide');
        // console.log($(window).width());
        var slideWidth = $(window).width() * 0.9;
        // var slideWidth = $(window).width();
        // console.log(slideWidth);
        for (var i = 0; i < swiper_slide.length; i++) {
            swiper_slide[i].style.width = slideWidth + 'px';
            swiper_slide[i].style.height = "160px";
        }
        $('.swiper-container').css('width',slideWidth + 'px');
        $('.swiper-wrapper').css({ 'transform': "translate3d(" + (-slideWidth) + "px, 0px, 0px)" })
        swiper_container[0].style.overflow = 'visible';
        var width80 = slideWidth * 0.9;
        $('.swiper-slide').css({ 'width': slideWidth + 'px' });*/
        var swiper = new Swiper('.swiper-container', {
            autoplay: 2000,
            speed: 500,
            autoplayDisableOnInteraction: false,
            loop: true,
            grabCursor : true,
            parallax:true,
            slidesPerView: 1,
            pagination: '.swiper-pagination'//分页容器的css选择器
            // onSlideChangeStart: function(swiper){
            //     // console.log('start'+swiper.activeIndex);
            // },
            // onSlideChangeEnd: function(swiper) {
            //     if (swiper.activeIndex == activeIndex) {
            //         // console.log(swiper.activeIndex);
            //         // console.log('end'+swiper.activeIndex);
            //         swiper.slideNext(false,500);
            //         }
            // }

        });

    }

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
    $.ajax({
        type:'get',
        url:'/api/article/index?cid=12&len=2',
        success:function(res){
            $('.sourcescontent').html(sourcesCmp(res.data.list))
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

                //最新动态
                var newsData = res.data.article;
                for(var i = 0; i<newsData.length; i++){
                    newsData = newsData.slice(0,2);
                    newsData[i].published_time = data_format(newsData[i].published_time);
                }
                $('#newsContent').html(newsCmp(newsData));
                for(var i = 0; i<newsData.length; i++){
                    if(newsData[i].is_top == 1){
                        $('.zhiding').eq(i).show();
                    }else{
                        $('.zhiding').eq(i).hide();
                    }
                }

                //活动信息
                var activityData = res.data.activity;
                for(var i = 0; i<activityData.length; i++){
                    activityData = activityData.slice(0,2);
                    // newsData[i].published_time = data_format(newsData[i].published_time);
                }
                $('#activityList').html(activityCmp(activityData));
                //获取当前时间转为时间戳
                var nowdata = Date.parse(new Date(getNowFormatDate()))/1000;
                // console.log(nowdata)
                for(var i = 0; i<activityData.length; i++){
                    // var timestamp = Date.parse(new Date(activityData[i].aend_time));
                    var timestamp = activityData[i].baoming_end_time;
                    // console.log(timestamp)
                    if(nowdata > timestamp){
                        $('.baomingState').eq(i).hide();
                    }else{
                        $('.baomingState').eq(i).show();
                    }
                }
                //文化点单
                var cultureData = res.data.culture;
                for(var i = 0; i<cultureData.length; i++){
                    cultureData = cultureData.slice(0,2);
                    // newsData[i].published_time = data_format(newsData[i].published_time);
                }
                $('#cultureList').html(cultureCmp(cultureData));


            }

        }
    });

})
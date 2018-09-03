$(function(){
    // function nav(navwidth){
    //     $.ajax({
    //         url: '/api/category/menu',
    //         dataType: 'json',
    //         async: false,
    //         success: function (res) {
    //             var liwidth = 0;
    //             var mr = 0;
    //             var flag = true;
    //             if (res.status == 1) {
    //                 // console.log(document.domain);
    //                 if (window.location.href == 'http://' + document.domain + '/portal/my/index') {
    //                     var html = '<li><a id="nav0" href="/">首页</a></li>';
    //
    //                 } else {
    //                     var html = '<li><a id="nav0" class="active" href="/">首页</a></li>';
    //                 }
    //                 $('.nav').append(html);
    //                 liwidth += $('.nav>li').width();
    //                 for (var i in res.data) {
    //                     var item = res.data[i];
    //                     if (liwidth > navwidth) {
    //                         //更多
    //                         if(flag == true){
    //                             //去除当前最后一个nav item 添加更多item后将其加入更多的子列表中
    //                             var index = $('.nav li:last-child').index();
    //                             html = '<li class="more" style="position: relative;cursor: pointer;"><a href="javascript:;">更多</a><i class="layui-icon" style="font-size: 14px;margin-left: 3px;color:#fff;">&#xe61a;</i><ul class="navmore"></ul></li>';
    //                             var lastchild = '<li>'+$('.nav li:last-child').html()+'</li>';
    //                             $('.nav li:last-child').remove();
    //                             $('.nav').append(html);
    //                             lastchild += '<li><a id="nav' + item['id'] + '" href="' + item['url'] + '" target="' + item['target'] + '">' + item['title'] + '</a></li>';
    //                             $('.nav .navmore').append(lastchild);
    //                             flag = false;
    //                         }else{
    //                             console.log()
    //                             //往更多里写
    //                             html = '<li><a id   ="nav' + item['id'] + '" href="' + item['url'] + '" target="' + item['target'] + '">' + item['title'] + '</a></li>';
    //                             $('.nav .navmore').append(html)
    //                         }
    //                     } else {
    //                         html = '<li><a id="nav' + item['id'] + '" href="' + item['url'] + '" target="' + item['target'] + '">' + item['title'] + '</a></li>';
    //                         $('.nav').append(html);
    //                     }
    //                     liwidth += $('.nav>li:last-child').width() + 32;
    //
    //                 }
    //             }
    //         }
    //     })
    // }
    function nav(navwidth,outerWidth){
        $.ajax({
            url: '/api/navigation',
            dataType: 'json',
            async: false,
            success: function (res) {
                console.log('aaa')
                var liwidth = 0;
                var mr = 0;
                var reg = /\d+/;
                var flag = true;
                if (res.status == 1) {
                    if (window.location.href == 'https://' + document.domain + '/portal/my/index') {
                        var html = '<li><a id="nav0" href="/">首页</a></li>';

                    } else {
                        var html = '<li><a id="nav0" class="active" href="/">首页</a></li>';
                    }
                    $('.nav').append(html);
                    liwidth += $('.nav>li:first-child').width() + outerWidth;
                    for (var i in res.data) {
                        var item = res.data[i];
                        if (liwidth > navwidth) {
                            //更多
                            if(flag == true){
                                //去除当前最后一个nav item 添加更多item后将其加入更多的子列表中
                                // var index = $('.nav>li:last-child').index();
                                html = '<li class="more" style="position: relative;cursor: pointer;"><a href="javascript:;">更多<span class="arrowbot"></span><span class="arrowtop"></span></a><ul class="navmore"></ul></li>';
                                //超出第一个的一级导航
                                var lastchild = '<li>'+$('.nav>li:last-child').html()+'</li>';
                                //清除超出的一级导航
                                $('.nav>li:last-child').remove();
                                //添加更多
                                $('.nav').append(html);
                                //导航截取超出省略号
                                // setstr(item['name'],6)
                                lastchild += '<li><a id="nav' + item['id'] + '" href="' + setUrlVal(item['url'], 'nid', item['id']) + '" target="' + item['target'] + '">' + item['name'] + '</a></li>';
                                $('.nav .navmore').append(lastchild);
                                flag = false;
                            }else{
                                //往更多里写
                                html = '<li><a id="nav' + item['id'] + '" href="' + setUrlVal(item['url'], 'nid', item['id']) + '" target="' + item['target'] + '">' + item['name'] + '</a></li>';
                                $('.nav .navmore').append(html)
                            }
                        } else {
                            var subnav = item.sub_nav;
                            if(subnav.length != 0){
                                html = '<li><a id="nav' + item['id'] + '" href="' + setUrlVal(item['url'], 'nid', item['id']) + '" target="' + item['target'] + '">' + item['name'] + '<span class="arrowbot"></span><span class="arrowtop"></span></a><ul class="subnav slide"></ul></li>';
                                $('.nav').append(html);
                                for(var j in subnav){
                                    var subitem = subnav[j];
                                    var subnavHtml = '';
                                    //添加一级导航和二级导航
                                    subnavHtml += '<li><a id="subnav' + subitem['id'] + '" href="' + setUrlVal(subitem['url'], 'nid', item['id'] + ',' + subitem['id']) + '" target="' + subitem['target'] + '">' + subitem['name'] + '</a></li>';
                                    $("#nav" + item['id']).next('.subnav').append(subnavHtml);
                                }
                            }else{
                                //添加一级导航
                                html = '<li><a id="nav' + item['id'] + '" href="' + setUrlVal(item['url'], 'nid', item['id']) + '" target="' + item['target'] + '">' + item['name'] + '</a></li>';
                                $('.nav').append(html);
                            }

                        }
                        liwidth += $('.nav>li:last-child').width() + outerWidth;
                        // liwidth += $('.nav>li:last-child').outerWidth(true);

                    }
                }
            }
        })
        $('body').on('mouseover','.nav>li',function(){
            $(this).find('.subnav').show();
            $(this).find('.arrowbot').css('display','none');
            $(this).find('.arrowtop').css('display','inline-block');
        })
        $('body').on('mouseout','.nav>li',function(){
            $(this).find('.subnav').hide();
            $(this).find('.arrowbot').css('display','inline-block');
            $(this).find('.arrowtop').css('display','none');
        })
    }
    function header7nav(navwidth,outerWidth){
        $.ajax({
            // url: '/api/category/menu',
            url: '/api/navigation',
            dataType: 'json',
            async: false,
            success: function (res) {
                var liwidth = 0;
                var mr = 0;
                var reg = /\d+/;
                var flag = true;
                if (res.status == 1) {
                    if (window.location.href == 'https://' + document.domain + '/portal/my/index') {
                        var html = '<li><a id="nav0" href="/"><span>首页</span></a></li>';

                    } else {
                        var html = '<li><a id="nav0" class="active" href="/"><span>首页</span></a></li>';
                    }
                    $('.nav').append(html);
                    liwidth += $('.nav>li:first-child').width() + outerWidth;
                    for (var i in res.data) {
                        var item = res.data[i];
                        if (liwidth > navwidth) {
                            //更多
                            if(flag == true){
                                //去除当前最后一个nav item 添加更多item后将其加入更多的子列表中
                                // var index = $('.nav>li:last-child').index();
                                html = '<li class="more" style="position: relative;cursor: pointer;"><a href="javascript:;"><span>更多</span></a><ul class="navmore"></ul></li>';
                                //超出第一个的一级导航
                                var lastchild = '<li>'+$('.nav>li:last-child').html()+'</li>';
                                //清除超出的一级导航
                                $('.nav>li:last-child').remove();
                                //添加更多
                                $('.nav').append(html);
                                lastchild += '<li><a id="nav' + item['id'] + '" href="' + setUrlVal(item['url'], 'nid', item['id']) + '" target="' + item['target'] + '">' + item['name'] + '</a></li>';
                                $('.nav .navmore').append(lastchild);
                                flag = false;
                            }else{
                                //往更多里写
                                html = '<li><a id="nav' + item['id'] + '" href="' + setUrlVal(item['url'], 'nid', item['id']) + '" target="' + item['target'] + '">' + item['name'] + '</a></li>';
                                $('.nav .navmore').append(html)
                            }
                        } else {
                            var subnav = item.sub_nav;
                            if(subnav.length != 0){
                                html = '<li><a id="nav' + item['id'] + '" href="' + setUrlVal(item['url'], 'nid', item['id']) + '" target="' + item['target'] + '"><span>' + item['name'] + '</span></a><ul class="subnav"></ul></li>';
                                $('.nav').append(html);
                                for(var j in subnav){
                                    var subitem = subnav[j];
                                    var subnavHtml = '';
                                    //添加一级导航和二级导航
                                    subnavHtml += '<li><a id="subnav' + subitem['id'] + '" href="' + setUrlVal(subitem['url'], 'nid', item['id'] + ',' + subitem['id']) + '" target="' + subitem['target'] + '">' + subitem['name'] + '</a></li>';
                                    $("#nav" + item['id']).next('.subnav').append(subnavHtml);
                                }
                            }else{
                                //添加一级导航
                                html = '<li><a id="nav' + item['id'] + '" href="' + setUrlVal(item['url'], 'nid', item['id']) + '" target="' + item['target'] + '"><span>' + item['name'] + '</span></a></li>';
                                $('.nav').append(html);
                            }

                        }
                        liwidth += $('.nav>li:last-child').width() + outerWidth;

                    }
                }
            }
        })
        $('body').on('mouseover','.nav>li',function(){
            $(this).find('.subnav').show();
            // $(this).find('.arrowbot').css('display','none');
            // $(this).find('.arrowtop').css('display','inline-block');
        })
        $('body').on('mouseout','.nav>li',function(){
            $(this).find('.subnav').hide();
            // $(this).find('.arrowbot').css('display','inline-block');
            // $(this).find('.arrowtop').css('display','none');
        })
    }
    //header
    function headerModule(template,data){
        console.log(template);
        var templateTpl = $('#'+template).html();
        var templateCmp = Handlebars.compile(templateTpl);
        $('#header').html(templateCmp(data))
        // var templateCmp = Handlebars.compile(templateTpl);
        // $('#header').html(templateCmp(data))
        if(template == 'headerModule1-temeplate'){
            nav(1150,32);
        }else if(template == 'headerModule2-temeplate'){
            nav(650,32);
        }else if(template == 'headerModule3-temeplate'){
            //headerM3对应banner模板
            $.ajax({
                type: 'get',
                url: '/api/category/getdata',
                dataType: 'json',
                async:false,
                data:{
                    id:6,
                    start:0
                },
                success:function(bannerdata){
                    if(bannerdata.status == 1){
                        var bannerhtml = '';
                        for(var i = 0 ; i<bannerdata.data.length;i++){
                            bannerhtml += '<div class="swiper-slide">';
                            bannerhtml += '<a href="'+ bannerdata.data[i].url+'">' +
                                '<img style="height:450px;" src="'+ bannerdata.data[i].thumb +'">' +
                                '</a>' +
                                '</div>';
                        }
                        $('.headerModule-3 .swiper-wrapper').html(bannerhtml);


                    }
                }
            })
            var header3swiper = new Swiper('.headerModule-3 .swiper-container', {
                // autoplay: 2000,
                // speed: 500,
                autoplayDisableOnInteraction: false,
                // // direction: 'vertical',
                // loop: true,
                // effect : 'fade',
                // fade: {
                //     crossFade: false,
                // },
                // slidesPerView: 1,
                // 如果需要分页器
                pagination : '.swiper-pagination3',
                // paginationType : 'bullets',
                // paginationClickable :true,
                grabCursor: true,
                paginationClickable: true
            });
            nav(1150,32);
        }else if(template == 'headerModule4-temeplate'){
            nav(900,54);
        }else if(template == 'headerModule5-temeplate'){
            nav(900,0);
        }else if(template == 'headerModule6-temeplate'){
            nav(1100,85);
        }else if(template == 'headerModule7-temeplate'){
            header7nav(800,0);
            //获取当前日期
            var dayNames = new Array("星期日","星期一","星期二","星期三","星期四","星期五","星期六");
            Stamp = new Date();
            $('.time').text( Stamp.getFullYear() + "年"+(Stamp.getMonth() + 1) +"月"+Stamp.getDate()+ "日"+ " " + dayNames[Stamp.getDay()] )
            //判断字数显示不同的高度
            var textdom = $('.headerModule-7-bot .nav>li>a>span');
            textdom.each(function(i){
                if(textdom.eq(i).text().length==3 || textdom.eq(i).text().length>=5){
                    textdom.eq(i).css('height','66px')
                }else{
                    textdom.eq(i).css('height','44px')
                }
            })
        }else if(template == 'headerModule8-temeplate'){
            nav(1150,44);
        }else if(template == 'headerModule9-temeplate'){
            header7nav(1180,48);
            //获取当前日期
            var dayNames = new Array("星期日","星期一","星期二","星期三","星期四","星期五","星期六");
            Stamp = new Date();
            $('.time').text( Stamp.getFullYear() + "年"+(Stamp.getMonth() + 1) +"月"+Stamp.getDate()+ "日"+ " " + dayNames[Stamp.getDay()] );
            var navmoreWidth = (40+27)*$('.navmore>li').length+27
            console.log(navmoreWidth)
            $('.navmore').css('width',navmoreWidth+'px');
        }
        checkLogin();
        setLogo();
    }
    function setLogo(){
        $.ajax({
            url:'/api/baseinfo/read',
            dataType:'json',
            async: false,
            success:function(res){
                if(res.status == 1){
                    $('.logo img').attr('src', res.data.home_page_logo);
                    $('img.logo').attr('src', res.data.home_page_logo);
                }
            }
        })
    }
    $.ajax({
        type: 'get',
        url: '/api/homepage/pc_global',
        dataType: 'json',
        async: false,
        success: function(res) {
            console.log(res)
            if(res.status==1){
                // $('#global-css').append(res.data.css);
                $('#global-css').append(res.data.header_css);
                headerModule(res.data.header_alias,res.data);
                $('.load').hide();
                $('.footer').show();
                search();
            }
        }
    })
    //搜索
    function search() {
        $('body').on('click', '.sousuo', function() {
            var searchval = $('#sousuoinput').val() || '';
            searchval = searchval.trim();
            if (searchval == '') {
                return false;
            }
            window.location.href = "/portal/search/index/?kv=" + searchval;
        });
        //回车键
        $(window).keydown(function(event) {
            if (event.keyCode == 13) {
                var searchval = $('#sousuoinput').val() || '';
                searchval = searchval.trim();
                if (searchval != '') {
                    window.location.href = "/portal/search/index/?kv=" + searchval;
                }
            }
        })
    }
})

function getCookie(cname)
{
  var name = cname + "=";
  var ca = document.cookie.split(';');
  for(var i=0; i<ca.length; i++) 
  {
    var c = ca[i].trim();
    if (c.indexOf(name)==0) return c.substring(name.length,c.length);
  }
  return "";
}
$(function(){
	$('#nav22').click(function(e){
		 e.preventDefault();
		 var ahref = $(this).attr('href');
		 request({
            url: '/api/proxy/user_login',
            type: 'post',
            dataType: 'json',
            //data: { token: token },
            success: function (res) {
                console.log(res);
                if (res.status == 1) {
                    window.location.href=ahref; 
                }else{
                    if(res.code  == 10005){
                        alert('请登录')
                    }else{
                        alert('登录异常');
                    }
                }
            },
            error: function (res) {

            }
        }, true);
	});	
})
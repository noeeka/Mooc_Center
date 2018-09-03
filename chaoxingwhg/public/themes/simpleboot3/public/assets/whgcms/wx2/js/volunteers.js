$(function() {
    // banner
    $.ajax({
        type: 'get',
        url: '/api/Volunteer/getBanner',
        dataType: 'json',
        async: false,
        success: function(res) {
            // console.log(res);
            if(res.status == 1){
                var volbannerTpl = $('#volbanner-item').html();
                var volbannerCmp = Handlebars.compile(volbannerTpl);
                $('#swiper-wrapper').html(volbannerCmp(res.data));
                var swiper = new Swiper('.swiper-container', {
                    autoplay: 2000,
                    speed: 500,
                    loop: true,
                    pagination: '.swiper-pagination',
                    paginationClickable: true
                });


            }

        }
    });
    // 活动报道
    $.ajax({
        type: 'get',
        url: '/api/Volunteer/getReport',
        dataType: 'json',
        async: false,
        success: function(res) {
            // console.log(res);
            if(res.status == 1){
                var data = res.data.slice(0,2)
                for(var i = 0; i<data.length; i++){
                    // newsData = newsData.slice(0,2);
                    data[i].published_time = data_format(data[i].published_time);
                }
                var reportsTpl = $('#reports-item').html();
                var reportsCmp = Handlebars.compile(reportsTpl);
                $('#reportsContent').html(reportsCmp(data));


            }

        }
    });
    // 活动招募
    $.ajax({
        type: 'get',
        url: '/api/Volunteer/getrecurit',
        dataType: 'json',
        async: false,
        success: function(res) {
            // console.log(res);
            if(res.status == 1){
                var data = res.data.slice(0,2)
                for(var i = 0; i<data.length; i++){
                    data[i].start_time = data_format(data[i].start_time);
                    data[i].end_time = data_format(data[i].end_time);
                }
                var zhaomuTpl = $('#zhaomu-item').html();
                var zhaomuCmp = Handlebars.compile(zhaomuTpl);
                $('#zhaomuContent').html(zhaomuCmp(data));
                //获取当前时间转为时间戳
                var nowdata = Date.parse(new Date(getNowFormatDate()))/1000;
                console.log(nowdata)
                for(var i = 0; i<data.length; i++){
                    var timestamp = data[i].baoming_end_time;
                    if(nowdata > timestamp){
                        console.log(123)
                        $('.baomingbtn').eq(i).text('已过期').css('background-color','#ddd');
                    }else{
                        $('.baomingbtn').eq(i).text('报名').css('background-color','#EB6877');
                    }
                }

            }

        }
    });
    // 风采展示
    $.ajax({
        type: 'get',
        url: '/api/Volunteer/getMien',
        dataType: 'json',
        async: false,
        success: function(res) {
            // console.log(res);
            if(res.status == 1){
                var data = res.data.slice(0,2)
                for(var i = 0; i<data.length; i++){
                    // newsData = newsData.slice(0,2);
                    data[i].published_time = data_format(data[i].published_time);
                    if(data[i].imgs != null){
                        data[i].imgs = data[i].imgs[0]
                    }
                }
                var fencaiTpl = $('#fencai-item').html();
                var fencaiCmp = Handlebars.compile(fencaiTpl);
                $('#fengcaiContent').html(fencaiCmp(data));


            }

        }
    });
    // 志愿者排行
    $.ajax({
        type: 'get',
        url: '/api/Volunteer/VolunScore',
        dataType: 'json',
        async: false,
        success: function(res) {
            // console.log(res);
            if(res.status == 1){
                var data = res.data.slice(0,10)
                for(var i = 0; i<data.length; i++){
                    // newsData = newsData.slice(0,2);
                    data[i].published_time = data_format(data[i].published_time);
                    if(data[i].more != null){
                        data[i].more = data[i].more[0]
                    }
                    if(data[i].avatar == ''){
                        data[i].avatar = '/themes/simpleboot3/public/assets/whgcms/wx/images/myphoto.png';
                    }
                }
                var VolunScoreTpl = $('#VolunScore-item').html();
                var VolunScoreCmp = Handlebars.compile(VolunScoreTpl);
                $('#VolunScore').html(VolunScoreCmp(data));


            }

        }
    });
    // 注册志愿者人数
    $.ajax({
        type: 'get',
        url: '/api/Volunteer/volun_count',
        dataType: 'json',
        async: false,
        success: function(res) {
            console.log(res);
            if(res.status == 1){
                var num = res.data.num.toString();
                // console.log(num)
                var numArr = num.split("");
                // console.log(numArr);
                var numHtml = '';
                for(var i = 0;i<numArr.length;i++){
                    numHtml += '<span>'+numArr[i]+'</span>'
                }
                $('#volunCount').html('已注册志愿者'+numHtml+' 人');

            }

        }
    });
    // 注册志愿者
    var token = getCookie('token');
    console.log(token)
    request({
        type: 'get',
        url: '/api/volunteer/auth_read',
        dataType: 'json',
        async: false,
        success: function(res) {
            console.log(res);
            if(res.code == 13012){
                $('.reg a').attr('href','javascript:;').text('审核中');
            }else if(res.code == 13011){
                $('.reg a').attr('href','javascript:;').text('已注册');
            }else if(token == null){
                $('.reg a').attr('href','javascript:;').text('注册');
                $('.reg a').click(function(){
                    alert('请登录后再操作！');
                })

            }else{
                 $('.reg a').text('注册');
            }

        }
    },true);
})